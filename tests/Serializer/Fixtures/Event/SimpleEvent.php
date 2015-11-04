<?php
namespace Tests\Boekkooi\Broadway\Serializer\Fixtures\Event;

final class SimpleEvent
{
    public $simpleId;
    public $someRandomData;
    public $ignoreNoneConstructorSetProperty = 'I won\'t serialize';
    protected $ignoreProtected = 'I won\'t serialize';
    private $ignorePrivate = 'I won\'t serialize';

    public function __construct($simpleId, $someRandomData)
    {
        $this->simpleId = $simpleId;
        $this->someRandomData = $someRandomData;
    }

    public function setIgnoreMethods($value)
    {
        throw new \LogicException('I should never be called!');
    }

    public function getIgnoreMethods($value)
    {
        throw new \LogicException('I should never be called!');
    }
}
