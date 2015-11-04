<?php
namespace Tests\Boekkooi\Broadway\Serializer\Fixtures\Event;

class ArrayHintedEvent
{
    /**
     * @var array
     */
    public $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }
}
