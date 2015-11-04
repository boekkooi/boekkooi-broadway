<?php
namespace Tests\Boekkooi\Broadway\Serializer\Normalizer;

use Boekkooi\Broadway\Serializer\Normalizer\PhoneNumberNormalizer;
use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberUtil;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PhoneNumberNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PhoneNumberNormalizer
     */
    private $normalizer;
    /**
     * @var PhoneNumberUtil
     */
    private $numberUtil;

    protected function setUp()
    {
        if (!class_exists(PhoneNumber::class)) {
            self::markTestSkipped('"giggsey/libphonenumber-for-php" is not installed');
        }
        if (!interface_exists(NormalizerInterface::class)) {
            self::markTestSkipped('"symfony/validator" is not installed');
        }

        $this->normalizer = new PhoneNumberNormalizer();
        $this->numberUtil = PhoneNumberUtil::getInstance();
    }

    public function testInterface()
    {
        self::assertInstanceOf(NormalizerInterface::class, $this->normalizer);
        self::assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    public function testNormalize()
    {
        $str = '+31208100215';
        $obj = $this->numberUtil->parse('+31208100215', PhoneNumberUtil::UNKNOWN_REGION);

        self::assertEquals($str, $this->normalizer->normalize($obj, 'json'));
        self::assertEquals($str, $this->normalizer->normalize($obj, 'xml'));
    }

    public function testSupportsNormalization()
    {
        self::assertTrue($this->normalizer->supportsNormalization(
            $this->numberUtil->parse('+31208100210', PhoneNumberUtil::UNKNOWN_REGION)
        ));

        self::assertFalse($this->normalizer->supportsNormalization(new PhoneNumber()), 'Invalid number');
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testDenormalize()
    {
        $obj = $this->numberUtil->parse('+31508100210', PhoneNumberUtil::UNKNOWN_REGION);
        $str = '+31508100210';

        self::assertEquals($obj, $this->normalizer->denormalize($str, 'json'));
        self::assertEquals($obj, $this->normalizer->denormalize($str, 'xml'));
    }

    public function testSupportsDenormalization()
    {
        self::assertFalse($this->normalizer->supportsDenormalization(array(), PhoneNumber::class), 'Unsupported data');
        self::assertFalse($this->normalizer->supportsDenormalization('+31505368823', 'stdClass'), 'Unsupported class');
        self::assertFalse($this->normalizer->supportsDenormalization('not-a-number', PhoneNumber::class), 'Invalid string');

        self::assertTrue($this->normalizer->supportsDenormalization('+31508100210', PhoneNumber::class));
    }
}
