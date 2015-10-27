<?php
namespace Tests\Boekkooi\Broadway\UuidGenerator\Testing;

use Rhumsaa\Uuid\Uuid;
use Boekkooi\Broadway\UuidGenerator\Testing\MockUuidSequenceGenerator;

class MockUuidSequenceGeneratorTest extends \PHPUnit_Framework_TestCase
{
    private static $uuids = [
        'e2d0c739-0001-434c-8d7a-03e29b400566',
        'e2d0c739-0002-434c-8d7a-03e29b400566',
        'e2d0c739-0003-434c-8d7a-03e29b400566',
        'e2d0c739-0004-434c-8d7a-03e29b400566',
    ];

    /**
     * @inheritdoc
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

        foreach (self::$uuids as $i => $uuid) {
            self::$uuids[$i] = Uuid::fromString($uuid);
        }
    }


    /**
     * @test
     */
    public function it_generates_a_uuid()
    {
        $generator = $this->createMockUuidGenerator();
        $uuid      = $generator->generate();

        self::assertInstanceOf(Uuid::class, $uuid);
    }

    /**
     * @test
     */
    public function it_generates_the_same_string()
    {
        $generator = $this->createMockUuidGenerator();

        foreach (self::$uuids as $uuid) {
            self::assertEquals($uuid, $generator->generate());
        }
    }

    /**
     * @test
     *
     * @expectedException \RuntimeException
     */
    public function it_throws_an_exception_when_pool_is_empty()
    {
        $generator = $this->createMockUuidGenerator();

        for ($i = 0; $i < 5; $i++) {
            $generator->generate();
        }
    }

    private function createMockUuidGenerator()
    {
        return new MockUuidSequenceGenerator(self::$uuids);
    }
}
