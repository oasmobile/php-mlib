<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-28
 * Time: 18:33
 */

namespace Oasis\Mlib\Event;

/**
 * Class EventDispatcherTrait
 *
 * @package   Oasis\Mlib\Event
 */
trait EventDispatcherTrait
{
    /** @var EventDispatcherInterface */
    protected $eventParent = null;
    /** @var array */
    protected $eventListeners = [];

    public function getParent()
    {
        return $this->eventParent;
    }

    public function setParent(EventDispatcherInterface $parent)
    {
        $this->eventParent = $parent;
    }

    public function dispatch($event)
    {
        if (!$event instanceof Event) {
            $event = new Event(strval($event));
        }

        if ($event->getTarget() == null) {
            /** @noinspection PhpParamsInspection */
            $event->setTarget($this);
        }
        /** @noinspection PhpParamsInspection */
        $event->setCurrentTarget($this);

        if ($this->eventListeners[$event->getName()]) {
            foreach ($this->eventListeners[$event->getName()] as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    call_user_func($callback, $event);

                    if ($event->isPropogationStoppedImmediately()) {
                        return;
                    }
                }
            }
        }

        if ($this->getParent() && $event->doesBubble() && !$event->isPropogationStopped()) {
            $this->getParent()->dispatch($event);
        }
    }

    public function addEventListener($name, callable $listener, $priority = 0)
    {
        if (!is_array($this->eventListeners[$name])) {
            $this->eventListeners[$name] = [];
        }
        if (!is_array($this->eventListeners[$name][$priority])) {
            $this->eventListeners[$name][$priority] = [];
            ksort($this->eventListeners[$name]);
        }

        $this->eventListeners[$name][$priority][] = $listener;
    }

    public function removeEventListener($name, callable $listener)
    {
        $comp = function ($a, $b) {
            if (is_string($a) && is_string($b) && $a == $b) {
                return true;
            }

            if (is_array($a) && is_array($b) && count($a) == count($b)) {
                if ($a[0] == $b[0] && $a[1] == $b[1]) {
                    return true;
                }
            }

            return $a === $b;
        };

        if (is_array($this->eventListeners[$name])) {
            foreach ($this->eventListeners[$name] as $priority => &$list) {
                $new_list = [];
                foreach ($list as $callback) {
                    if (!$comp($callback, $listener)) {
                        $new_list[] = $callback;
                    }
                }
                $list = $new_list;
            }
        }
    }

}
