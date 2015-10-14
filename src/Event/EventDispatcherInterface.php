<?php
/**
 * Created by PhpStorm.
 * User: minhao
 * Date: 2015-09-28
 * Time: 18:25
 */

namespace Oasis\Mlib\Event;

interface EventDispatcherInterface
{
    /**
     * @return EventDispatcherInterface
     */
    public function getParentEventDispatcher();

    /**
     * @param EventDispatcherInterface $parent
     */
    public function setParentEventDispatcher(EventDispatcherInterface $parent);

    /**
     * Dispatches a event
     *
     * @param Event|string $event
     *
     * @return mixed
     */
    public function dispatch($event);

    public function addEventListener($name, callable $listener, $priority = 0);

    public function removeEventListener($name, callable $listener);

    public function removeAllEventListeners($name = '');

    public function setDelegateDispatcher($delegate);
}
