<?php
namespace Boekkooi\Broadway\Serializer\Normalizer;

use Boekkooi\Broadway\Serializer\Denormalizer\AdvancedInstantiationTrait;
use Symfony\Component\Serializer\Mapping\AttributeMetadata;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;

/**
 * Serialization groups are not support for events.
 */
class EventNormalizer extends PropertyNormalizer
{
    use AdvancedInstantiationTrait;

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        $allowedAttributes = $this->getAllowedAttributes($class, $context, true);
        $normalizedData = $this->prepareForDenormalization($data);

        $reflectionClass = new \ReflectionClass($class);
        $object = $this->instantiateObject($normalizedData, $class, $context, $reflectionClass, $allowedAttributes);

        return $object;
    }

    /**
     * @inheritdoc
     */
    public function getAllowedAttributes($classOrObject, array $context, $attributesAsString = false)
    {
        $class = new \ReflectionClass(is_object($classOrObject) ? get_class($classOrObject) : $classOrObject);
        $parameters = $class->getConstructor()->getParameters();

        $attributes = [];
        foreach ($parameters as $parameter) {
            $attributes[] = $attributesAsString ? $parameter->getName() : new AttributeMetadata($parameter->getName());
        }

        return $attributes;
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && !$data instanceof \Traversable && $this->supports(get_class($data));
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && $this->supports($type);
    }

    /**
     * Checks if the given class has any non-static public property and a constructor with arguments.
     *
     * @param string $class
     *
     * @return bool
     */
    private function supports($class)
    {
        $class = new \ReflectionClass($class);

        if (($constructor = $class->getConstructor()) === null || count($constructor->getParameters()) === 0) {
            return false;
        }

        // We look for at least one non-static property
        foreach ($class->getProperties() as $property) {
            if (!$property->isStatic()) {
                return true;
            }
        }

        return false;
    }
}
