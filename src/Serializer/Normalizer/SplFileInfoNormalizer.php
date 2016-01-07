<?php
namespace Boekkooi\Broadway\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer for DateTime and DateTimeImmutable objects.
 */
class SplFileInfoNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     *
     * @param \SplFileInfo $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->getPathname();
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \SplFileInfo;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if ($data === null) {
            return null;
        }

        return new \SplFileInfo($data);
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return \SplFileInfo::class === $type && is_string($data);
    }
}
