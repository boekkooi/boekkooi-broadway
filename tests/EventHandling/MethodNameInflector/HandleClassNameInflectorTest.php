<?php
namespace Tests\Boekkooi\Broadway\EventHandling\MethodNameInflector;

use Boekkooi\Broadway\EventHandling\MethodNameInflector\HandleClassNameInflector;
use Tests\Boekkooi\Broadway\Fixtures\Event\EventWithoutNamespace;
use Tests\Boekkooi\Broadway\Fixtures\Event\FinishedEvent;

class HandleClassNameInflectorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var HandleClassNameInflector
     */
    private $inflector;

    protected function setUp()
    {
        $this->inflector = new HandleClassNameInflector();
    }

    public function testHandlesClassesWithoutNamespace()
    {
        $command = new EventWithoutNamespace();

        self::assertEquals(
            'handleEventWithoutNamespace',
            $this->inflector->inflect($command, new \stdClass())
        );
    }

    public function testHandlesNamespacedClasses()
    {
        $command = new FinishedEvent();

        self::assertEquals(
            'handleFinishedEvent',
            $this->inflector->inflect($command, new \stdClass())
        );
    }
}
