<?php
namespace Boekkooi\Broadway\Testing;

use Symfony\Component\Validator\Mapping\ClassMetadataInterface;
use Symfony\Component\Validator\Mapping\MetadataInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

trait SymfonyValidatorTestTrait
{
    /**
     * Get a validator instance.
     *
     * @return ValidatorInterface
     */
    protected static function getValidator()
    {
        throw new \LogicException('Please implement `getValidator()`');
    }

    /**
     * Get the validator metadata for a class.
     *
     * @param string $class A class FQCN
     * @return ClassMetadataInterface|null
     */
    protected static function getValidatorClassMetadata($class)
    {
        $validator = static::getValidator();
        if (!$validator->hasMetadataFor($class)) {
            return null;
        }

        $metadata = static::getValidator()->getMetadataFor($class);
        if (!$metadata instanceof ClassMetadataInterface) {
            return null;
        }

        return $metadata;
    }

    /**
     * Get the constraints attached to a property.
     *
     * @param ClassMetadataInterface $classMetadata
     * @param string $property
     * @return \Symfony\Component\Validator\Constraint[]
     */
    protected static function getClassPropertyConstraints(ClassMetadataInterface $classMetadata, $property)
    {
        if (!$classMetadata->hasPropertyMetadata($property)) {
            return [];
        }

        $constraints = [];
        foreach ($classMetadata->getPropertyMetadata($property) as $metadata) {
            if (!$metadata instanceof MetadataInterface) {
                continue;
            }

            foreach ($metadata->getConstraints() as $constraint) {
                $constraints[] = $constraint;
            }
        }

        return $constraints;
    }

    /**
     * Get the implicit validation group based on a class name.
     *
     * @param string $className
     * @return string
     */
    protected static function getClassImplicitGroupName($className)
    {
        $classGroup = $className;
        if (($p = strrpos($classGroup, '\\')) !== false) {
            $classGroup = substr($classGroup, $p + 1);
        }

        return $classGroup;
    }

    /**
     * Assert that a class has the exact set of constraints.
     *
     * @param string $className
     * @param string[]|\Symfony\Component\Validator\Constraint[] $expectedConstraints
     * @param string $message
     * @param bool $autoConfigureGroups
     */
    protected static function assertClassConstraints($className, array $expectedConstraints, $message = '', $autoConfigureGroups = true)
    {
        $metadata = static::getValidatorClassMetadata($className);

        /* @noinspection PhpUndefinedMethodInspection */
        self::assertThat(
            $metadata->getConstraints(),
            new Constraint\isEqualConstraints($expectedConstraints, $autoConfigureGroups, static::getClassImplicitGroupName($className)),
            $message
        );
    }

    /**
     * Assert that a class property has the exact set of constraints.
     *
     * @param string $className
     * @param string $propertyName
     * @param string[]|\Symfony\Component\Validator\Constraint[] $expectedConstraints
     * @param string $message
     * @param bool $autoConfigureGroups
     */
    protected static function assertClassPropertyConstraints($className, $propertyName, array $expectedConstraints, $message = '', $autoConfigureGroups = true)
    {
        $constraints = static::getClassPropertyConstraints(
            static::getValidatorClassMetadata($className),
            $propertyName
        );

        /* @noinspection PhpUndefinedMethodInspection */
        self::assertThat(
            $constraints,
            new Constraint\isEqualConstraints($expectedConstraints, $autoConfigureGroups, static::getClassImplicitGroupName($className)),
            $message
        );
    }
}
