<?php
namespace Boekkooi\Broadway\EventHandling\Exception;

/**
 * Thrown when a specific event handler object can not be used on a event.
 *
 * The most common reason is the receiving method is missing or incorrectly named.
 */
class CanNotInvokeHandlerException extends \BadMethodCallException implements Exception
{
    /**
     * @var object
     */
    private $event;

    /**
     * @param mixed $event
     * @param string $reason
     *
     * @return static
     */
    public static function forEvent($event, $reason)
    {
        $exception = new static(
            'Could not invoke handler for event ' . get_class($event) .
            ' for reason: ' . $reason
        );
        $exception->event = $event;

        return $exception;
    }

    /**
     * Returns the event that could not be invoked
     *
     * @return object
     */
    public function getEvent()
    {
        return $this->event;
    }
}
