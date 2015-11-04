<?php
namespace Tests\Boekkooi\Broadway\Serializer\Normalizer;

use Boekkooi\Broadway\Serializer\Normalizer\DateTimeZoneNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeZoneNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DateTimeZoneNormalizer
     */
    private $normalizer;

    protected function setUp()
    {
        if (!interface_exists(NormalizerInterface::class)) {
            self::markTestSkipped('"symfony/validator" is not installed');
        }

        $this->normalizer = new DateTimeZoneNormalizer();
    }

    public function testInterface()
    {
        self::assertInstanceOf(NormalizerInterface::class, $this->normalizer);
        self::assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    public function testNormalize()
    {
        $str = 'UTC';
        $obj = new \DateTimeZone('UTC');

        self::assertEquals($str, $this->normalizer->normalize($obj, 'json'));
        self::assertEquals($str, $this->normalizer->normalize($obj, 'xml'));
    }

    public function testSupportsNormalization()
    {
        self::assertTrue($this->normalizer->supportsNormalization(new \DateTimeZone('Europe/London')));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testDenormalize()
    {
        $str = 'Europe/Amsterdam';
        $obj = new \DateTimeZone('Europe/Amsterdam');

        self::assertEquals($obj, $this->normalizer->denormalize($str, 'json'));
        self::assertEquals($obj, $this->normalizer->denormalize($str, 'xml'));
    }

    public function testSupportsDenormalization()
    {
        self::assertFalse($this->normalizer->supportsDenormalization(array(), \DateTimeZone::class), 'Unsupported data');
        self::assertFalse($this->normalizer->supportsDenormalization('UTC', 'stdClass'), 'Unsupported class');
        self::assertFalse($this->normalizer->supportsDenormalization('Mars/Phobos', \DateTimeZone::class), 'Invalid timezone');

        self::assertTrue($this->normalizer->supportsDenormalization('UTC', \DateTimeZone::class));
    }
}
