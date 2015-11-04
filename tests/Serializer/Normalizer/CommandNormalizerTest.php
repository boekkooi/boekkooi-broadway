<?php
namespace Tests\Boekkooi\Broadway\Serializer\Normalizer;

use Boekkooi\Broadway\Serializer\Normalizer\CommandNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tests\Boekkooi\Broadway\Serializer\Fixtures\Command\SimpleCommand;
use Tests\Boekkooi\Broadway\Serializer\Fixtures\Command\TypeHintedCommand;
use Tests\Boekkooi\Broadway\Serializer\Fixtures\SerializerDummy;

class CommandNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var CommandNormalizer
     */
    private $normalizer;

    protected function setUp()
    {
        if (!interface_exists(NormalizerInterface::class)) {
            self::markTestSkipped('"symfony/validator" is not installed');
        }

        $this->normalizer = new CommandNormalizer();
    }

    public function testInterface()
    {
        self::assertInstanceOf(NormalizerInterface::class, $this->normalizer);
        self::assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    public function testNormalize()
    {
        $obj = new SimpleCommand('id', 'data');
        $obj->setExtraData('extra');
        $obj->publicData = 'public';

        self::assertEquals(
            [
                'constructId' => 'id',
                'getData' => 'data',
                'extraData' => 'extra',
                'publicData' => 'public',
            ],
            $this->normalizer->normalize($obj)
        );
    }

    public function testNormalizeTypeHintedAttributes()
    {
        $data = new \stdClass();
        $data->val = 'yay';

        $obj = new TypeHintedCommand();
        $obj->setData($data);

        /** @var \PHPUnit_Framework_MockObject_MockObject|SerializerDummy $serializer */
        $serializer = $this->getMock(SerializerDummy::class);
        $serializer
            ->expects(self::atLeastOnce())
            ->method('normalize')
            ->with($data, null, array('circular_reference_limit' => [ spl_object_hash($obj) => 1 ]))
            ->willReturn('std_was_normalized');

        $this->normalizer->setSerializer($serializer);

        self::assertEquals(
            [
                'data' => 'std_was_normalized',
            ],
            $this->normalizer->normalize($obj)
        );
    }

    public function testSupportsNormalization()
    {
        self::assertFalse($this->normalizer->supportsNormalization(array()), 'Invalid data');
        self::assertFalse($this->normalizer->supportsNormalization(new \ArrayIterator()), 'Not supporting traversable data');

        self::assertTrue($this->normalizer->supportsNormalization(new SimpleCommand('id', 'data')));
    }

    public function testDenormalize()
    {
        $obj = new SimpleCommand('id', 'data');
        $obj->setExtraData('extra');
        $obj->publicData = 'public';

        self::assertEquals(
            $obj,
            $this->normalizer->denormalize([
                'constructId' => 'id',
                'getData' => 'data',
                'extraData' => 'extra',
                'publicData' => 'public',
            ], SimpleCommand::class)
        );
    }

    public function testDenormalizeTypeHintedAttributes()
    {
        $data = new \stdClass();

        /** @var \PHPUnit_Framework_MockObject_MockObject|SerializerDummy $serializer */
        $serializer = $this->getMock(SerializerDummy::class);
        $serializer
            ->expects(self::atLeastOnce())
            ->method('denormalize')
            ->with('std', \stdClass::class, null, array())
            ->willReturn($data);

        $this->normalizer->setSerializer($serializer);

        $obj = new TypeHintedCommand();
        $obj->setData($data);

        $result = $this->normalizer->denormalize([ 'data' => 'std', ], TypeHintedCommand::class);
        self::assertEquals($obj, $result);
        self::assertSame($data, $result->getData());
    }

    public function testSupportsDenormalization()
    {
        self::assertFalse($this->normalizer->supportsDenormalization(array(), \stdClass::class), 'No properties or methods');
        self::assertTrue($this->normalizer->supportsDenormalization(array(), SimpleCommand::class));
    }
}
