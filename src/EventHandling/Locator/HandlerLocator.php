<?php
namespace Boekkooi\Broadway\EventHandling\Locator;

/**
 * Service locator for handler objects
 *
 * This interface is often a wrapper around your frameworks dependency
 * injection container or just maps event names to handler names on disk somehow.
 */
interface HandlerLocator
{
    /**
     * Retrieves the handlers for a specified event
     *
     * @param string $eventName
     *
     * @return \Traversable
     */
    public function getHandlersForEvent($eventName);
}
