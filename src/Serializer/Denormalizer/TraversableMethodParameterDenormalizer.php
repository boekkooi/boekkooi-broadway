<?php
namespace Boekkooi\Broadway\Serializer\Denormalizer;

use Symfony\Component\Serializer\Exception\LogicException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerAwareInterface;
use Symfony\Component\Serializer\SerializerInterface;

class TraversableMethodParameterDenormalizer implements DenormalizerInterface, SerializerAwareInterface
{
    /**
     * @var DenormalizerInterface|null
     */
    private $denormalizer;
    /**
     * @var SerializerInterface|null
     */
    private $serializer;

    /**
     * @var array
     */
    protected $classMethodParameterMap = [];

    public function __construct(DenormalizerInterface $itemDenormalizer = null)
    {
        $this->denormalizer = $itemDenormalizer;
    }

    /**
     * @inheritdoc
     */
    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
    }

    public function registerMethodParameter($className, $methodName, $parameterName, $parameterItemClassName)
    {
        $serializerType = $className . '::' . $methodName . '(' . $parameterName . ')';
        $this->classMethodParameterMap[$serializerType] = $parameterItemClassName;
    }

    /**
     * {@inheritdoc}
     * @param array|\Traversable $data
     */
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        if ($data === null) {
            return null;
        }

        $denormalizer = $this->denormalizer ?: $this->serializer;
        if (!$denormalizer instanceof DenormalizerInterface) {
            throw new LogicException('Cannot denormalize because injected serializer is not a denormalizer');
        }

        $childClass = $this->classMethodParameterMap[$type];

        $res = [];
        foreach ($data as $child) {
            $res[] = $denormalizer->denormalize($child, $childClass, $format, $context);
        }
        return $res;
    }

    /**
     * Checks whether the given class is supported for denormalization by this normalizer.
     *
     * @param mixed $data Data to denormalize from.
     * @param string $type The class to which the data should be denormalized.
     * @param string $format The format being deserialized from.
     *
     * @return bool
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return
            ($data === null || is_array($data) || $data instanceof \Traversable) &&
            isset($this->classMethodParameterMap[$type])
        ;
    }
}
