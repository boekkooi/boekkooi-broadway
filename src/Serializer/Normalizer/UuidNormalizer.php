<?php
namespace Boekkooi\Broadway\Serializer\Normalizer;

use Rhumsaa\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer for Uuid objects.
 */
class UuidNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->toString();
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Uuid;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return Uuid::fromString($data);
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Uuid::class === $type &&
            is_string($data) &&
            Uuid::isValid($data)
        ;
    }
}
