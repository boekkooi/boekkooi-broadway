<?php
namespace Boekkooi\Broadway\Serializer\Normalizer;

use libphonenumber\PhoneNumber;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;
use Rhumsaa\Uuid\Uuid;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * A normalizer for PhoneNumber objects.
 */
class PhoneNumberNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function normalize($object, $format = null, array $context = [])
    {
        return PhoneNumberUtil::getInstance()->format($object, PhoneNumberFormat::E164);
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return
            $data instanceof PhoneNumber &&
            PhoneNumberUtil::getInstance()->isValidNumber($data)
        ;
    }

    /**
     * @inheritdoc
     */
    public function denormalize($data, $class, $format = null, array $context = [])
    {
        return PhoneNumberUtil::getInstance()->parse($data, PhoneNumberUtil::UNKNOWN_REGION);
    }

    /**
     * @inheritdoc
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            PhoneNumber::class === $type &&
            is_string($data) &&
            PhoneNumberUtil::getInstance()->isPossibleNumber($data, PhoneNumberUtil::UNKNOWN_REGION)
        ;
    }
}
