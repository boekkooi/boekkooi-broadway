<?php
namespace Boekkooi\Broadway\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class JsonSerializableNormalizer implements NormalizerInterface
{
    /**
     * @inheritdoc
     */
    public function normalize($object, $format = null, array $context = array())
    {
        return $object->jsonSerialize();
    }

    /**
     * @inheritdoc
     */
    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof \JsonSerializable;
    }
}
