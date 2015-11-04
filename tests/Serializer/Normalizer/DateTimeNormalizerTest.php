<?php
namespace Tests\Boekkooi\Broadway\Serializer\Normalizer;

use Boekkooi\Broadway\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class DateTimeNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var DateTimeNormalizer
     */
    private $normalizer;

    protected function setUp()
    {
        if (!interface_exists(NormalizerInterface::class)) {
            self::markTestSkipped('"symfony/validator" is not installed');
        }

        $this->normalizer = new DateTimeNormalizer();
    }

    public function testInterface()
    {
        self::assertInstanceOf(NormalizerInterface::class, $this->normalizer);
        self::assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    public function testNormalize()
    {
        $str = '2015-03-12T14:17:19.176169+00:00';
        $obj = new \DateTime('2015-03-12T14:17:19.176169+00:00');

        self::assertEquals($str, $this->normalizer->normalize($obj, 'json'));
        self::assertEquals($str, $this->normalizer->normalize($obj, 'xml'));

        $str = '2015-10-01T10:09:23.000000+02:00';
        $obj = new \DateTimeImmutable('2015-10-01T10:09:23+02:00');

        self::assertEquals($str, $this->normalizer->normalize($obj, 'json'));
        self::assertEquals($str, $this->normalizer->normalize($obj, 'xml'));
    }

    public function testSupportsNormalization()
    {
        self::assertTrue($this->normalizer->supportsNormalization(new \DateTime()));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testDenormalize()
    {
        $str = '2015-10-01T10:09:23.000000+02:00';
        $obj = new \DateTimeImmutable('2015-10-01T10:09:23+02:00');

        self::assertEquals($obj, $this->normalizer->denormalize($str, 'json'));
        self::assertEquals($obj, $this->normalizer->denormalize($str, 'xml'));

        $str = '2015-10-01T10:09:23.176169+08:00';
        $obj = new \DateTimeImmutable('2015-10-01T10:09:23.176169+08:00');

        self::assertEquals($obj, $this->normalizer->denormalize($str, 'json'));
        self::assertEquals($obj, $this->normalizer->denormalize($str, 'xml'));
    }

    public function testSupportsDenormalization()
    {
        self::assertFalse($this->normalizer->supportsDenormalization(array(), \DateTime::class), 'Unsupported data');
        self::assertFalse($this->normalizer->supportsDenormalization(array(), \DateTimeImmutable::class), 'Unsupported data');
        self::assertFalse($this->normalizer->supportsDenormalization('2015-10-01T10:09:23.000000+02:00', 'stdClass'), 'Unsupported class');

        self::assertTrue($this->normalizer->supportsDenormalization('2015-10-01T10:09:23.000000+02:00', \DateTime::class));
        self::assertTrue($this->normalizer->supportsDenormalization('2015-10-01T10:09:23.000000+02:00', \DateTimeImmutable::class));
    }
}
