<?php
namespace Boekkooi\Broadway\Testing;

use Boekkooi\Broadway\Serializer\SymfonySerializer;
use Broadway\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Serializer;

trait SymfonySerializerTestTrait
{
    /**
     * @return SerializerInterface
     */
    protected static function getSerializer()
    {
        $serializer = new Serializer(
            static::getSerializerNormalizers()
        );

        return new SymfonySerializer($serializer, $serializer);
    }

    /**
     * @return NormalizerInterface[]
     */
    protected static function getSerializerNormalizers()
    {
        throw new \LogicException('Please implement `getSerializerNormalizers()`');
    }
}
