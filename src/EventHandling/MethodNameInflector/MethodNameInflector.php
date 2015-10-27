<?php
namespace Boekkooi\Broadway\EventHandling\MethodNameInflector;

/**
 * Deduce the method name to call on the event handler based on the event and handler instances.
 */
interface MethodNameInflector
{
    /**
     * Return the method name to call on the event handler and return it.
     *
     * @param mixed $event
     * @param object $eventHandler
     *
     * @return string
     */
    public function inflect($event, $eventHandler);
}
