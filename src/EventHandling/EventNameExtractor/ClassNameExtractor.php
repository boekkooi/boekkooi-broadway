<?php
namespace Boekkooi\Broadway\EventHandling\EventNameExtractor;

/**
 * Extract the name from the class
 */
class ClassNameExtractor implements EventNameExtractor
{
    /**
     * @inheritdoc
     */
    public function extract($event)
    {
        return get_class($event);
    }
}
