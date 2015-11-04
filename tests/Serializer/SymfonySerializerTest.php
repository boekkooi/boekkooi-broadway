<?php
namespace Tests\Boekkooi\Broadway\Serializer;

use Boekkooi\Broadway\Serializer\SymfonySerializer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SymfonySerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NormalizerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $normalizerMock;
    /**
     * @var DenormalizerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $denormalizerMock;
    /**
     * @var SymfonySerializer
     */
    private $serializer;

    public function setUp()
    {
        if (!interface_exists(NormalizerInterface::class)) {
            self::markTestSkipped('"symfony/validator" is not installed');
        }

        $this->normalizerMock = $this->getMock(NormalizerInterface::class);
        $this->denormalizerMock = $this->getMock(DenormalizerInterface::class);

        $this->serializer = new SymfonySerializer($this->normalizerMock, $this->denormalizerMock);
    }

    /**
     * @test
     */
    public function it_serializes_objects_by_passing_then_to_the_normalizer()
    {
        $object = new \stdClass();

        $this->normalizerMock
            ->expects(self::atLeastOnce())
            ->method('normalize')
            ->with($object, 'json')
            ->willReturn(spl_object_hash($object));

        self::assertEquals(
            array(
                'type'   => 'stdClass',
                'payload' => spl_object_hash($object)
            ),
            $this->serializer->serialize($object)
        );
    }

    /**
     * @test
     */
    public function it_deserializes_objects_by_passing_then_to_the_denormalizer()
    {
        $data = array(
            'type' => 'aFlyingDuck',
            'payload' => array('duck_name' => 'bob')
        );
        $result = new \stdClass();

        $this->denormalizerMock
            ->expects(self::atLeastOnce())
            ->method('denormalize')
            ->with(array('duck_name' => 'bob'), 'aFlyingDuck', 'json')
            ->willReturn($result);

        self::assertSame(
            $result,
            $this->serializer->deserialize($data)
        );
    }

    /**
     * @test
     * @expectedException \Assert\InvalidArgumentException
     * @expectedExceptionMessage Key 'type' should be set
     */
    public function it_throws_an_exception_if_type_not_set_in_data()
    {
        $this->serializer->deserialize(array('payload' => ''));
    }

    /**
     * @test
     * @expectedException \Assert\InvalidArgumentException
     * @expectedExceptionMessage Key 'payload' should be set
     */
    public function it_throws_an_exception_if_payload_not_set_in_data()
    {
        $this->serializer->deserialize(array('type' => 'SomeClass'));
    }
}
