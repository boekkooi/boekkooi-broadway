<?php
namespace Boekkooi\Broadway\Serializer;

use Assert\Assertion as Assert;
use Broadway\Serializer\SerializationException;
use Broadway\Serializer\SerializerInterface as BroadwaySerializerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class SymfonySerializer implements BroadwaySerializerInterface
{
    /**
     * @var NormalizerInterface
     */
    private $normalizer;
    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    public function __construct(
        NormalizerInterface $normalizer,
        DenormalizerInterface $denormalizer
    ) {
        $this->normalizer = $normalizer;
        $this->denormalizer = $denormalizer;
    }

    /**
     * @return array
     *
     * @throws SerializationException
     */
    public function serialize($object)
    {
        return array(
            // TODO implement TypeNormalizer & Resolver
            'type'   => get_class($object),
            'payload' => $this->normalizer->normalize($object, 'json')
        );
    }

    /**
     * @param array $serializedObject
     *
     * @return mixed
     *
     * @throws SerializationException
     */
    public function deserialize(array $serializedObject)
    {
        Assert::keyExists($serializedObject, 'type', "Key 'type' should be set.");
        Assert::keyExists($serializedObject, 'payload', "Key 'payload' should be set.");

        // TODO implement TypeDenormalizer
        return $this->denormalizer->denormalize($serializedObject['payload'], $serializedObject['type'], 'json');
    }
}
