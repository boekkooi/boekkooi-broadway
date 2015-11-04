<?php
namespace Boekkooi\Broadway\Serializer\Denormalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Exception\RuntimeException;
use Symfony\Component\Serializer\Exception\LogicException;

/**
 * @property \Symfony\Component\Serializer\NameConverter\NameConverterInterface $nameConverter
 * @property \Symfony\Component\Serializer\SerializerInterface $serializer
 * @property array $ignoredAttributes
 */
trait AdvancedInstantiationTrait
{
    /**
     * Denormalize a method parameter value.
     *
     * @param \ReflectionParameter $reflectionParameter
     * @param mixed $value Parameter value to denormalize
     * @return mixed
     */
    protected function denormalizeParameter(\ReflectionParameter $reflectionParameter, $value)
    {
        if ($reflectionParameter->getClass() === null && !$reflectionParameter->isArray()) {
            return $value;
        }

        if (!$this->serializer instanceof DenormalizerInterface) {
            throw new LogicException('Cannot denormalize because injected serializer is not a denormalizer');
        }

        if ($reflectionParameter->getClass() !== null) {
            return $this->serializer->denormalize(
                $value,
                $reflectionParameter->getClass()->name
            );
        }

        if ($reflectionParameter->isArray()) {
            $className = $reflectionParameter->getDeclaringClass()->getName();
            $methodName = $reflectionParameter->getDeclaringFunction()->getName();
            $parameterName = $reflectionParameter->getName();

            return $this->serializer->denormalize(
                $value,
                $className . '::' . $methodName . '(' . $parameterName . ')'
            );
        }

        return $value;
    }


    /**
     * This is a override of AbstractNormalizer.
     *
     * @see \Symfony\Component\Serializer\Normalizer\AbstractNormalizer::instantiateObject
     *
     * @inheritdoc
     */
    protected function instantiateObject(array &$data, $class, array &$context, \ReflectionClass $reflectionClass, $allowedAttributes)
    {
        if (
            isset($context['object_to_populate']) &&
            is_object($context['object_to_populate']) &&
            $class === get_class($context['object_to_populate'])
        ) {
            return $context['object_to_populate'];
        }

        $constructor = $reflectionClass->getConstructor();
        if ($constructor) {
            $constructorParameters = $constructor->getParameters();

            $params = array();
            foreach ($constructorParameters as $constructorParameter) {
                $paramName = $constructorParameter->name;
                $key = $this->nameConverter ? $this->nameConverter->normalize($paramName) : $paramName;

                $allowed = $allowedAttributes === false || in_array($paramName, $allowedAttributes);
                $ignored = in_array($paramName, $this->ignoredAttributes);
                if (method_exists($constructorParameter, 'isVariadic') && $constructorParameter->isVariadic()) {
                    if ($allowed && !$ignored && (isset($data[$key]) || array_key_exists($key, $data))) {
                        if (!is_array($data[$paramName])) {
                            throw new RuntimeException(sprintf('Cannot create an instance of %s from serialized data because the variadic parameter %s can only accept an array.', $class, $constructorParameter->name));
                        }

                        $params = array_merge($params, $data[$paramName]);
                    }
                } elseif ($allowed && !$ignored && (isset($data[$key]) || array_key_exists($key, $data))) {
                    /*------ The following differs from the original method: ------*/
                    $params[] = $this->denormalizeParameter($constructorParameter, $data[$key]);
                    /*------ Done ------*/

                    // don't run set for a parameter passed to the constructor
                    unset($data[$key]);
                } elseif ($constructorParameter->isDefaultValueAvailable()) {
                    $params[] = $constructorParameter->getDefaultValue();
                } else {
                    throw new RuntimeException(
                        sprintf(
                            'Cannot create an instance of %s from serialized data because its constructor requires parameter "%s" to be present.',
                            $class,
                            $constructorParameter->name
                        )
                    );
                }
            }

            return $reflectionClass->newInstanceArgs($params);
        }

        return new $class();
    }
}
