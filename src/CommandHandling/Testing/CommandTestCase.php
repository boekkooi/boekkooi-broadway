<?php
namespace Boekkooi\Broadway\CommandHandling\Testing;

use Boekkooi\Broadway\Serializer\Normalizer;
use Boekkooi\Broadway\Testing\SymfonySerializerTestTrait;
use Boekkooi\Broadway\Testing\SymfonyValidatorAnnotationTestTrait;
use Boekkooi\Broadway\Testing\SymfonyValidatorTestTrait;
use Symfony\Component\Validator\Validation;

abstract class CommandTestCase extends \PHPUnit_Framework_TestCase
{
    use SymfonyValidatorTestTrait;
    use SymfonyValidatorAnnotationTestTrait;
    use SymfonySerializerTestTrait;

    protected static $validator;

    public static function setUpBeforeClass()
    {
        static::$validator = null;
        static::registerValidatorAnnotations();
    }

    /**
     * @inheritdoc
     */
    protected static function getValidator()
    {
        static::$validator;

        if (static::$validator === null) {
            static::$validator = Validation::createValidatorBuilder()
                ->enableAnnotationMapping()
                ->getValidator();
        }

        return static::$validator;
    }

    /**
     * @inheritdoc
     */
    protected static function getSerializerNormalizers()
    {
        return [
            new Normalizer\UuidNormalizer(),
            new Normalizer\SplFileInfoNormalizer(),
            new Normalizer\DateTimeNormalizer(),
            new Normalizer\DateTimeZoneNormalizer(),
            new Normalizer\JsonSerializableNormalizer(),
            new Normalizer\CommandNormalizer(),
        ];
    }

    /**
     * @dataProvider provideCommandClassInstances
     * @param object $command
     */
    public function testCommandSerialization($command)
    {
        $serializer = static::getSerializer();

        $serialized = $serializer->serialize($command);
        $deserialized = $serializer->deserialize($serialized);

        static::assertEquals($command, $deserialized);
    }

    public function testAllCommandPropertyConstraintsShouldBeTested()
    {
        $testedProperties = array_map(
            function(array $arr) { return $arr[0]; },
            $this->providePropertyConstraints()
        );

        $refl = new \ReflectionClass($this->getCommandClass());
        $properties = array_map(
            function(\ReflectionProperty $prop) { return $prop->getName(); },
            $refl->getProperties()
        );

        static::assertEquals(
            [],
            array_diff($properties, $testedProperties),
            'All properties should be tested for constraints'
        );
    }

    /**
     * @dataProvider providePropertyConstraints
     * @param string $property
     * @param \Symfony\Component\Validator\Constraint[] $constraints
     */
    public function testCommandPropertyConstraints($property, array $constraints)
    {
        static::assertClassPropertyConstraints(
            $this->getCommandClass(),
            $property,
            $constraints
        );
    }

    /**
     * The FQCN of the command to test
     *
     * @return string
     */
    abstract protected function getCommandClass();

    /**
     * A phpunit dataProvider giving a set of command instances to test serialization.
     *
     * For example:
     * return [
     *    [ new MyCommand('data') ],
     * };
     *
     * @return array
     */
    abstract public function provideCommandClassInstances();

    /**
     * A phpunit dataProvider giving a property and a set constraints that it must have.
     *
     * For example:
     * return [
     *    [ 'orderId', [ Constraints\NotNull::class, ] ],
     * };
     *
     * @return array
     */
    abstract public function providePropertyConstraints();
}
