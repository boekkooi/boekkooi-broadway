<?php
namespace Tests\Boekkooi\Broadway\UuidGenerator\Testing;

use Rhumsaa\Uuid\Uuid;
use Boekkooi\Broadway\UuidGenerator\Testing\MockUuidGenerator;

class MockUuidGeneratorTest extends \PHPUnit_Framework_TestCase
{
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

        for ($i = 0; $i < 5; $i++) {
            self::assertEquals(Uuid::fromString('e2d0c739-53ac-434c-8d7a-03e29b400566'), $generator->generate());
        }
    }

    private function createMockUuidGenerator()
    {
        return new MockUuidGenerator(Uuid::fromString('e2d0c739-53ac-434c-8d7a-03e29b400566'));
    }
}
