<?php
namespace Boekkooi\Broadway\EventHandling\Exception;

class CannotDetermineEventNameException extends \RuntimeException implements Exception
{
    /**
     * @var mixed
     */
    private $event;

    /**
     * @param mixed $event
     *
     * @return static
     */
    public static function forEvent($event)
    {
        $exception = new static(sprintf(
            'Could not determine event name of %s',
            is_object($event) ? get_class($event) : gettype($event)
        ));
        $exception->event = $event;

        return $exception;
    }

    /**
     * Returns the event of which the name could not be extracted
     *
     * @return mixed
     */
    public function getEvent()
    {
        return $this->event;
    }
}
