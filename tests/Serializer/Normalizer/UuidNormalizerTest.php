<?php
namespace Tests\Boekkooi\Broadway\Serializer\Normalizer;

use Boekkooi\Broadway\Serializer\Normalizer\UuidNormalizer;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UuidNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UuidNormalizer
     */
    private $normalizer;

    protected function setUp()
    {
        if (!class_exists(Uuid::class)) {
            self::markTestSkipped('ramsey/uuid is not installed');
        }
        if (!interface_exists(NormalizerInterface::class)) {
            self::markTestSkipped('"symfony/validator" is not installed');
        }

        $this->normalizer = new UuidNormalizer();
    }

    public function testInterface()
    {
        self::assertInstanceOf(NormalizerInterface::class, $this->normalizer);
        self::assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    public function testNormalize()
    {
        $str = '10e1cb59-6dde-491d-adaa-ca5a8ddc7ccc';
        $obj = Uuid::fromString('10e1cb59-6dde-491d-adaa-ca5a8ddc7ccc');

        self::assertEquals($str, $this->normalizer->normalize($obj, 'json'));
        self::assertEquals($str, $this->normalizer->normalize($obj, 'xml'));
    }

    public function testSupportsNormalization()
    {
        self::assertTrue($this->normalizer->supportsNormalization(Uuid::fromString('10e1cb59-6dde-491d-adaa-ca5a8ddc7ccc')));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testDenormalize()
    {
        $obj = Uuid::fromString('6d6f9c73-1f59-4235-ace1-3bccd789315d');
        $str = '6d6f9c73-1f59-4235-ace1-3bccd789315d';

        self::assertEquals($obj, $this->normalizer->denormalize($str, 'json'));
        self::assertEquals($obj, $this->normalizer->denormalize($str, 'xml'));
    }

    public function testSupportsDenormalization()
    {
        self::assertFalse($this->normalizer->supportsDenormalization(array(), Uuid::class), 'Unsupported data');
        self::assertFalse($this->normalizer->supportsDenormalization('6d6f9c73-1f59-4235-ace1-3bccd789315d', 'stdClass'), 'Unsupported class');
        self::assertFalse($this->normalizer->supportsDenormalization('not-a-uuid', Uuid::class), 'Invalid string');

        self::assertTrue($this->normalizer->supportsDenormalization('59765a95-935b-4e5e-bebf-388a5704c0fc', Uuid::class));
    }
}
