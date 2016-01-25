<?php
namespace Boekkooi\Broadway\Serializer\Normalizer;

use SebastianBergmann\Money\Currency;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer for Money objects.
 */
class CurrencyNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @inheritdoc
     * @param Currency $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->getCurrencyCode();
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Currency;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if ($data === null) {
            return null;
        }

        return new Currency($data);
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Currency::class === $type &&
            ($data === null || is_string($data))
        ;
    }
}
