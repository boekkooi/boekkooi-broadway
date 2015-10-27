<?php
namespace Tests\Boekkooi\Broadway\Fixtures\EventHandler;

use Tests\Boekkooi\Broadway\Fixtures\Event\FinishedEvent;

class ExampleEventHandler
{
    public function handleFinishedEvent(FinishedEvent $event)
    {
    }

    public function handleTestEvent()
    {
    }

    public static function handleNoStaticMethods()
    {
    }

    public function test()
    {
    }

    protected function handleHiddenProtected()
    {
    }

    private function handleHiddenPrivate()
    {
    }
}
