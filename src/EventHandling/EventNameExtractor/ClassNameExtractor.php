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
        $className = get_class($event);
        $ns = strrpos($className, '\\');

        return $ns === false ? $className : substr($className, $ns + 1);
    }
}
