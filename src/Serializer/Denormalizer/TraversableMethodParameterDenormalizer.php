<?php
namespace Boekkooi\Broadway\Serializer\Denormalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class TraversableMethodParameterDenormalizer implements DenormalizerInterface
{
    /**
     * @var DenormalizerInterface
     */
    private $denormalizer;

    /**
     * @var array
     */
    protected $classMethodParameterMap = [];

    public function __construct(DenormalizerInterface $itemDenormalizer)
    {
        $this->denormalizer = $itemDenormalizer;
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
        $childClass = $this->classMethodParameterMap[$type];

        $res = [];
        foreach ($data as $child) {
            $res[] = $this->denormalizer->denormalize($child, $childClass, $format, $context);
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
            (is_array($data) || $data instanceof \Traversable) &&
            isset($this->classMethodParameterMap[$type])
        ;
    }
}
