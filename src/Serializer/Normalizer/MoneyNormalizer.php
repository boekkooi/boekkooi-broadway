<?php
namespace Boekkooi\Broadway\Serializer\Normalizer;

use Rhumsaa\Uuid\Uuid;
use SebastianBergmann\Money\Currency;
use SebastianBergmann\Money\Money;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer for Money objects.
 */
class MoneyNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @inheritdoc
     * @param Money $object
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return $object->jsonSerialize();
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Money;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        if ($data === null) {
            return null;
        }

        return new Money($data['amount'], new Currency($data['currency']));
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            Money::class === $type &&
            ($data === null || (is_array($data) && isset($data['amount'], $data['currency'])))
        ;
    }
}
