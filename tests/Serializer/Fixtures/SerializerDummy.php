<?php
namespace Tests\Boekkooi\Broadway\Serializer\Fixtures;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

abstract class SerializerDummy implements SerializerInterface, NormalizerInterface, DenormalizerInterface
{
}
