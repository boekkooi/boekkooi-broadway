<?php
namespace Tests\Boekkooi\Broadway\Serializer\Fixtures\Event;

class ClassHintedEvent
{
    /**
     * @var \stdClass
     */
    public $class;

    public function __construct(\stdClass $class)
    {
        $this->class = $class;
    }
}
