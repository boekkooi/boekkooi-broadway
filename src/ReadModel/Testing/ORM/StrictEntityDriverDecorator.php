<?php
namespace Boekkooi\Broadway\ReadModel\Testing\ORM;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriver;

/**
 * A driver decorator that restricts metadata access to a defined list of entities.
 */
class StrictEntityDriverDecorator implements MappingDriver
{
    /**
     * @var MappingDriver
     */
    private $wrapped;

    /**
     * @var string[]
     */
    private $entityClasses;

    /**
     * @param MappingDriver $wrapped
     * @param string[] $entityClasses
     */
    public function __construct(MappingDriver $wrapped, array $entityClasses)
    {
        $this->wrapped = $wrapped;
        $this->entityClasses =  array_map(
            function ($className) {
                return ltrim($className, '\\');
            },
            $entityClasses
        );
    }

    /**
     * @inheritdoc
     */
    public function getAllClassNames()
    {
        return array_intersect(
            $this->entityClasses,
            $this->wrapped->getAllClassNames()
        );
    }

    /**
     * @inheritdoc
     */
    public function loadMetadataForClass($className, ClassMetadata $metadata)
    {
        $className = ltrim($className, '\\');
        if (!in_array($className, $this->entityClasses, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Class "%s" is not within the allowed set of entities.',
                $className
            ));
        }

        $this->wrapped->loadMetadataForClass($className, $metadata);
    }

    /**
     * @inheritdoc
     */
    public function isTransient($className)
    {
        return $this->wrapped->isTransient($className);
    }
}
