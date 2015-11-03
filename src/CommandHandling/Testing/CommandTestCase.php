<?php
namespace Boekkooi\Broadway\CommandHandling\Testing;

use Boekkooi\Broadway\Testing\SymfonyValidatorAnnotationTestTrait;
use Boekkooi\Broadway\Testing\SymfonyValidatorTestTrait;
use Symfony\Component\Validator\Validation;

abstract class CommandTestCase extends \PHPUnit_Framework_TestCase
{
    use SymfonyValidatorTestTrait;
    use SymfonyValidatorAnnotationTestTrait;

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
}
