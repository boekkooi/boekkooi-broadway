<?php
namespace Tests\Boekkooi\Broadway\Serializer\Normalizer;

use Boekkooi\Broadway\Serializer\Normalizer\SplFileInfoNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SplFileInfoNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SplFileInfoNormalizer
     */
    private $normalizer;

    protected function setUp()
    {
        if (!interface_exists(NormalizerInterface::class)) {
            self::markTestSkipped('"symfony/validator" is not installed');
        }

        $this->normalizer = new SplFileInfoNormalizer();
    }

    public function testInterface()
    {
        self::assertInstanceOf(NormalizerInterface::class, $this->normalizer);
        self::assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    public function testNormalize()
    {
        $str = '/tmp/test.tmp';
        $obj = new \SplFileInfo('/tmp/test.tmp');

        self::assertEquals($str, $this->normalizer->normalize($obj, 'json'));
        self::assertEquals($str, $this->normalizer->normalize($obj, 'xml'));

        $str = __FILE__;
        $obj = new \SplFileInfo(__FILE__);

        self::assertEquals($str, $this->normalizer->normalize($obj, 'json'));
        self::assertEquals($str, $this->normalizer->normalize($obj, 'xml'));
    }

    public function testSupportsNormalization()
    {
        self::assertTrue($this->normalizer->supportsNormalization(new \SplFileInfo(__FILE__)));
        self::assertTrue($this->normalizer->supportsNormalization(new \SplFileInfo('a_none_existing_file.tmp')));
        self::assertFalse($this->normalizer->supportsNormalization(new \stdClass()));
    }

    public function testDenormalize()
    {
        $str = '/tmp/test.tmp';
        $obj = new \SplFileInfo('/tmp/test.tmp');

        self::assertEquals($obj, $this->normalizer->denormalize($str, 'json'));
        self::assertEquals($obj, $this->normalizer->denormalize($str, 'xml'));

        $str = __FILE__;
        $obj = new \SplFileInfo(__FILE__);

        self::assertEquals($obj, $this->normalizer->denormalize($str, 'json'));
        self::assertEquals($obj, $this->normalizer->denormalize($str, 'xml'));
    }

    public function testSupportsDenormalization()
    {
        self::assertFalse($this->normalizer->supportsDenormalization(array(), \SplFileInfo::class), 'Unsupported data');
        self::assertFalse($this->normalizer->supportsDenormalization(1, \SplFileInfo::class), 'Unsupported data');
        self::assertFalse($this->normalizer->supportsDenormalization(__FILE__, 'stdClass'), 'Unsupported class');

        self::assertTrue($this->normalizer->supportsDenormalization(__FILE__, \SplFileInfo::class));
        self::assertTrue($this->normalizer->supportsDenormalization('/tmp/test.txt', \SplFileInfo::class));
    }
}
