<?php
namespace Tests\Boekkooi\Broadway\Serializer\Fixtures\Command;

class TypeHintedCommand
{
    private $data;

    public function setData(\stdClass $data)
    {
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
}
