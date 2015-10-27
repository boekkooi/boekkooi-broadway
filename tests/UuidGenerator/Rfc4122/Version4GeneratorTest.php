<?php
namespace Tests\Boekkooi\Broadway\UuidGenerator\Rfc4122;

use Rhumsaa\Uuid\Uuid;
use Boekkooi\Broadway\UuidGenerator\Rfc4122\Version4Generator;

class Version4GeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function it_should_generate_a_uuid()
    {
        $generator = new Version4Generator();
        $uuid = $generator->generate();

        self::assertInstanceOf(Uuid::class, $uuid);
    }

    /**
     * @test
     */
    public function it_should_generate_a_version_4_uuid()
    {
        $generator = new Version4Generator();
        $uuid = $generator->generate();

        self::assertEquals(4, $uuid->getVersion());
    }
}
