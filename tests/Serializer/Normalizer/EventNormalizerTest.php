<?php
namespace Tests\Boekkooi\Broadway\Serializer\Normalizer;

use Boekkooi\Broadway\Serializer\Normalizer\EventNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Tests\Boekkooi\Broadway\Serializer\Fixtures\Event\ArrayHintedEvent;
use Tests\Boekkooi\Broadway\Serializer\Fixtures\Event\ClassHintedEvent;
use Tests\Boekkooi\Broadway\Serializer\Fixtures\Event\EmptyEvent;
use Tests\Boekkooi\Broadway\Serializer\Fixtures\Event\SimpleEvent;
use Tests\Boekkooi\Broadway\Serializer\Fixtures\SerializerDummy;

class EventNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EventNormalizer
     */
    private $normalizer;

    protected function setUp()
    {
        if (!interface_exists(NormalizerInterface::class)) {
            self::markTestSkipped('"symfony/validator" is not installed');
        }

        $this->normalizer = new EventNormalizer();
    }

    public function testInterface()
    {
        self::assertInstanceOf(NormalizerInterface::class, $this->normalizer);
        self::assertInstanceOf(DenormalizerInterface::class, $this->normalizer);
    }

    public function testNormalization()
    {
        $obj = new SimpleEvent('id', 'some data');

        self::assertEquals(
            [
                'simpleId' => 'id',
                'someRandomData' => 'some data'
            ],
            $this->normalizer->normalize($obj)
        );
    }

    public function testNormalizationIgnoresNoneConstructorAttributes()
    {
        $obj = new SimpleEvent('id', 'some data');
        $obj->ignoreNoneConstructorSetProperty = false;

        self::assertEquals(
            [
                'simpleId' => 'id',
                'someRandomData' => 'some data'
            ],
            $this->normalizer->normalize($obj)
        );
    }


    public function testSupportsNormalization()
    {
        self::assertFalse($this->normalizer->supportsNormalization(array()), 'Only objects supported');
        self::assertFalse($this->normalizer->supportsNormalization(new \ArrayIterator()), 'Not supporting traversable data');
        self::assertFalse($this->normalizer->supportsNormalization(new EmptyEvent()), 'No properties and constructor');

        self::assertTrue($this->normalizer->supportsNormalization(new SimpleEvent(null, null)));
    }

    public function testDenormalization()
    {
        $obj = new SimpleEvent('id', ['some data']);

        self::assertEquals(
            $obj,
            $this->normalizer->denormalize([
                'simpleId' => 'id',
                'someRandomData' => ['some data']
            ], SimpleEvent::class)
        );
    }

    public function testDenormalizationClassTypeHint()
    {
        $obj = new \stdClass();

        /** @var \PHPUnit_Framework_MockObject_MockObject|SerializerDummy $serializer */
        $serializer = $this->getMock(SerializerDummy::class);
        $serializer
            ->expects(self::atLeastOnce())
            ->method('denormalize')
            ->with('std', \stdClass::class, null, array())
            ->willReturn($obj);

        $this->normalizer->setSerializer($serializer);

        $result = $this->normalizer->denormalize(['class' => 'std'], ClassHintedEvent::class);
        self::assertEquals(new ClassHintedEvent($obj), $result);
        self::assertSame($obj, $result->class);
    }

    public function testDenormalizationArrayHintedAttributes()
    {
        $expectedValues = [ new \stdClass() ];

        /** @var \PHPUnit_Framework_MockObject_MockObject|SerializerDummy $serializer */
        $serializer = $this->getMock(SerializerDummy::class);
        $serializer
            ->expects(self::atLeastOnce())
            ->method('denormalize')
            ->with([ 'data' ], ArrayHintedEvent::class . '::__construct(values)', null, array())
            ->willReturn($expectedValues);

        $this->normalizer->setSerializer($serializer);

        $result = $this->normalizer->denormalize([ 'values' => [ 'data' ] ], ArrayHintedEvent::class);
        self::assertEquals(new ArrayHintedEvent($expectedValues), $result);
        self::assertSame($expectedValues, $result->values);
    }

    public function testDenormalizationIgnoresNoneConstructorAttributes()
    {
        $obj = new SimpleEvent('id', 'some data');

        self::assertEquals(
            $obj,
            $this->normalizer->denormalize([
                'simpleId' => 'id',
                'someRandomData' => 'some data',
                'ignoreNoneConstructorSetProperty' => false,
                'ignoreProtected' => false,
                'ignorePrivate' => false,
            ], SimpleEvent::class)
        );
    }

    public function testSupportsDenormalization()
    {
        self::assertFalse($this->normalizer->supportsDenormalization(array(), EmptyEvent::class), 'No properties and constructor');
        self::assertTrue($this->normalizer->supportsDenormalization(array(), SimpleEvent::class));
    }
}
