<?php
namespace Tests\Boekkooi\Broadway\Serializer\Fixtures\Command;

class SimpleCommand
{
    public $publicData;
    protected $constructId;
    private $getData;
    private $extraData;

    protected $ignoredProtected = 'i\'m ignored';
    private $ignoredPrivate = 'i\'m ignored';

    public function __construct($constructId, $getData)
    {
        $this->constructId = $constructId;
        $this->getData = $getData;
    }

    public function getConstructId()
    {
        return $this->constructId;
    }

    public function setConstructId($constructId)
    {
        throw new \LogicException('I should never be called!');
    }

    public function getGetData()
    {
        return $this->getData;
    }

    public function setExtraData($extraData)
    {
        $this->extraData = $extraData;
    }

    public function getExtraData()
    {
        return $this->extraData;
    }
}
