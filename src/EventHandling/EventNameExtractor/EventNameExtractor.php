<?php
namespace Boekkooi\Broadway\EventHandling\EventNameExtractor;

use Boekkooi\Broadway\EventHandling\Exception\CannotDetermineEventNameException;

/**
 * Extract the name from a event so that the name can be determined
 * by the context better than simply the class name
 */
interface EventNameExtractor
{
    /**
     * Extract the name from a event
     *
     * @param mixed $event
     *
     * @return string
     *
     * @throws CannotDetermineEventNameException
     */
    public function extract($event);
}
