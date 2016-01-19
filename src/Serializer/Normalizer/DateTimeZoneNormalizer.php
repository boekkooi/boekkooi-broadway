<?php
namespace Boekkooi\Broadway\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer for DateTimeZone objects.
 */
class DateTimeZoneNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->getName();
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \DateTimeZone;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if ($data === null) {
            return null;
        }

        return new \DateTimeZone($data);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            \DateTimeZone::class === $type &&
            (
                $data === null ||
                (is_string($data) && in_array($data, \DateTimeZone::listIdentifiers()))
            )
        ;
    }
}
