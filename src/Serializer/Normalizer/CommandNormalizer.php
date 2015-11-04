<?php
namespace Boekkooi\Broadway\Serializer\Normalizer;

use Boekkooi\Broadway\Serializer\Denormalizer\AdvancedInstantiationTrait;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Exception\LogicException;

class CommandNormalizer extends GetSetMethodNormalizer
{
    use AdvancedInstantiationTrait;

    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        if ($this->isCircularReference($object, $context)) {
            return $this->handleCircularReference($object);
        }

        $reflectionObject = new \ReflectionObject($object);
        $reflectionMethods = $reflectionObject->getMethods(\ReflectionMethod::IS_PUBLIC);
        $allowedAttributes = $this->getAllowedAttributes($object, $context, true);

        $attributes = array();

        /** @see \Symfony\Component\Serializer\Normalizer\PropertyNormalizer::normalize **/
        foreach ($reflectionObject->getProperties() as $property) {
            if (in_array($property->name, $this->ignoredAttributes)) {
                continue;
            }

            if (false !== $allowedAttributes && !in_array($property->name, $allowedAttributes)) {
                continue;
            }

            // Ignore none public properties
            if (!$property->isPublic() || $property->isStatic()) {
                continue;
            }

            $attributeValue = $property->getValue($object);

            if (isset($this->callbacks[$property->name])) {
                $attributeValue = call_user_func($this->callbacks[$property->name], $attributeValue);
            }
            if (null !== $attributeValue && !is_scalar($attributeValue)) {
                if (!$this->serializer instanceof NormalizerInterface) {
                    throw new LogicException(sprintf('Cannot normalize attribute "%s" because injected serializer is not a normalizer', $property->name));
                }

                $attributeValue = $this->serializer->normalize($attributeValue, $format, $context);
            }

            $propertyName = $property->name;
            if ($this->nameConverter) {
                $propertyName = $this->nameConverter->normalize($propertyName);
            }

            $attributes[$propertyName] = $attributeValue;
        }

        /** @see \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer::normalize **/
        foreach ($reflectionMethods as $method) {
            if ($this->isGetMethod($method)) {
                $attributeName = lcfirst(substr($method->name, 0 === strpos($method->name, 'is') ? 2 : 3));
                if (in_array($attributeName, $this->ignoredAttributes)) {
                    continue;
                }

                if (false !== $allowedAttributes && !in_array($attributeName, $allowedAttributes)) {
                    continue;
                }

                /*------ The following differs from the original method: ------*/
                if ($this->nameConverter) {
                    $attributeName = $this->nameConverter->normalize($attributeName);
                }

                if (array_key_exists($attributeName, $attributes)) {
                    continue;
                }
                /*------ Done ------*/

                $attributeValue = $method->invoke($object);
                if (isset($this->callbacks[$attributeName])) {
                    $attributeValue = call_user_func($this->callbacks[$attributeName], $attributeValue);
                }
                if (null !== $attributeValue && !is_scalar($attributeValue)) {
                    if (!$this->serializer instanceof NormalizerInterface) {
                        throw new LogicException(sprintf('Cannot normalize attribute "%s" because injected serializer is not a normalizer', $attributeName));
                    }

                    $attributeValue = $this->serializer->normalize($attributeValue, $format, $context);
                }

                $attributes[$attributeName] = $attributeValue;
            }
        }

        return $attributes;
    }


    /**
     * This is a override.
     *
     * @see \Symfony\Component\Serializer\Normalizer\ObjectNormalizer::denormalize
     * @see \Symfony\Component\Serializer\Normalizer\PropertyNormalizer::denormalize
     *
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        $allowedAttributes = $this->getAllowedAttributes($class, $context, true);
        $normalizedData = $this->prepareForDenormalization($data);

        $reflectionClass = new \ReflectionClass($class);
        $object = $this->instantiateObject($normalizedData, $class, $context, $reflectionClass, $allowedAttributes);

        $classMethods = get_class_methods($object);
        foreach ($normalizedData as $attribute => $value) {
            if ($this->nameConverter) {
                $attribute = $this->nameConverter->denormalize($attribute);
            }

            $allowed = $allowedAttributes === false || in_array($attribute, $allowedAttributes);
            $ignored = in_array($attribute, $this->ignoredAttributes);

            if ($allowed && !$ignored) {
                /*------ The following differs from the original method: ------*/
                if ($reflectionClass->hasProperty($attribute)) {
                    $property = $reflectionClass->getProperty($attribute);
                    if ($property->isPublic()) {
                        $property->setValue($object, $value);
                        continue;
                    }
                }

                $setter = 'set'.ucfirst($attribute);
                if (in_array($setter, $classMethods)) {
                    $parameters = $reflectionClass->getMethod($setter)->getParameters();
                    if (count($parameters) === 1) {
                        $value = $this->denormalizeParameter($parameters[0], $value);

                        $object->$setter($value);
                        continue;
                    }
                }
                /*------ Done ------*/
            }
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return is_object($data) && !$data instanceof \Traversable && $this->supports(get_class($data));
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return class_exists($type) && $this->supports($type);
    }

    /**
     * Checks if the given class has constructor with arguments or any non-static public properties.
     *
     * @param string $className
     *
     * @return bool
     */
    private function supports($className)
    {
        $class = new \ReflectionClass($className);

        // We look for at least one non-static property
        foreach ($class->getProperties() as $property) {
            if (!$property->isStatic() && $property->isPublic()) {
                return true;
            }
        }

        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
        foreach ($methods as $method) {
            if ($this->isGetMethod($method)) {
                return true;
            }
        }

        return false;
    }

    /**
     * This a a exact copy of parent::isGetMethod
     * @see \Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer
     */
    private function isGetMethod(\ReflectionMethod $method)
    {
        $methodLength = strlen($method->name);

        return
            !$method->isStatic() &&
            (
                ((0 === strpos($method->name, 'get') && 3 < $methodLength) ||
                (0 === strpos($method->name, 'is') && 2 < $methodLength)) &&
                0 === $method->getNumberOfRequiredParameters()
            )
        ;
    }
}
