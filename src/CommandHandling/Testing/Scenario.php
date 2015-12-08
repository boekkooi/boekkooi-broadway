<?php
namespace Boekkooi\Broadway\CommandHandling\Testing;

use Broadway\CommandHandling\CommandHandlerInterface;
use Broadway\CommandHandling\Testing\Scenario as BroadwayScenario;
use Broadway\EventStore\TraceableEventStore;
use PHPUnit_Framework_TestCase;

class Scenario extends BroadwayScenario
{
    /**
     * @var PHPUnit_Framework_TestCase
     */
    private $testCase;
    /**
     * @var TraceableEventStore
     */
    private $eventStore;

    public function __construct(
        PHPUnit_Framework_TestCase $testCase,
        TraceableEventStore $eventStore,
        CommandHandlerInterface $commandHandler)
    {
        parent::__construct($testCase, $eventStore, $commandHandler);

        $this->eventStore = $eventStore;
        $this->testCase = $testCase;
    }

    /**
     * @param array $events
     *
     * @return Scenario
     */
    public function thenCheck(\Closure $closure)
    {
        $closure($this->eventStore->getEvents(), $this->testCase);

        $this->eventStore->clearEvents();

        return $this;
    }
}
