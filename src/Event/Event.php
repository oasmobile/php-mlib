<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-28
 * Time: 18:25
 */

namespace Oasis\Mlib\Event;

class Event
{
    /** @var EventDispatcherInterface */
    protected $target;
    /** @var EventDispatcherInterface */
    protected $currentTarget;

    protected $name;
    protected $context;
    protected $bubbles;

    protected $propogationStopped            = false;
    protected $propogationStoppedImmediately = false;

    /**
     * Create an Event object
     *
     * @param string $name    name of the Event
     * @param mixed  $context context of the Event
     * @param bool   $bubbles whether the event should bubble (to parent dispatcher)
     */
    function __construct($name, $context = null, $bubbles = true)
    {
        $this->name    = $name;
        $this->context = $context;
        $this->bubbles = $bubbles;
    }

    public function stopImmediatePropogation()
    {
        $this->propogationStopped =
        $this->propogationStoppedImmediately = true;
    }

    public function stopPropogation()
    {
        $this->propogationStopped = true;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * @return boolean
     */
    public function doesBubble()
    {
        return $this->bubbles;
    }

    /**
     * @return boolean
     */
    public function isPropogationStopped()
    {
        return $this->propogationStopped;
    }

    /**
     * @return boolean
     */
    public function isPropogationStoppedImmediately()
    {
        return $this->propogationStoppedImmediately;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param EventDispatcherInterface $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getCurrentTarget()
    {
        return $this->currentTarget;
    }

    /**
     * @param EventDispatcherInterface $currentTarget
     */
    public function setCurrentTarget($currentTarget)
    {
        $this->currentTarget = $currentTarget;
    }

}
