<?php
namespace Boekkooi\Broadway\ReadModel\Testing;

use Broadway\Domain\DateTime;
use Broadway\Domain\DomainMessage;
use Broadway\Domain\Metadata;
use Broadway\EventHandling\EventListenerInterface;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * Helper testing scenario to test projects.
 *
 * The scenario will help with testing event handlers. A scenario consists of
 * three steps:
 *
 * 1) given(): Lets the projector handle some events
 * 2) when():  When a specific event is handled
 * 3) then():  The repository should contain these read models
 */
class Scenario
{
    /**
     * @var \PHPUnit_Framework_TestCase
     */
    private $testCase;
    /**
     * @var ObjectRepository
     */
    private $repository;
    /**
     * @var EventListenerInterface
     */
    private $eventListener;
    /**
     * @var int
     */
    private $playhead;
    /**
     * @var mixed
     */
    private $aggregateId;

    public function __construct(
        \PHPUnit_Framework_TestCase $testCase,
        ObjectRepository $repository,
        EventListenerInterface $eventListener
    ) {
        $this->testCase    = $testCase;
        $this->repository  = $repository;
        $this->eventListener = $eventListener;
        $this->playhead    = -1;
        $this->aggregateId = 1;
    }

    /**
     * @param string $aggregateId
     * @return Scenario
     */
    public function withAggregateId($aggregateId)
    {
        $this->aggregateId = $aggregateId;

        return $this;
    }

    /**
     * @param array $events
     * @return Scenario
     */
    public function given(array $events = [])
    {
        foreach ($events as $given) {
            $this->eventListener->handle($this->createDomainMessageForEvent($given));
        }

        return $this;
    }

    /**
     * @param mixed $event
     * @param DateTime $occurredOn
     * @return Scenario
     */
    public function when($event, DateTime $occurredOn = null)
    {
        $this->eventListener->handle($this->createDomainMessageForEvent($event, $occurredOn));

        return $this;
    }

    /**
     * @param array $expectedData
     * @return Scenario
     */
    public function then(array $expectedData)
    {
        $this->testCase->assertEquals($expectedData, $this->repository->findAll());

        return $this;
    }

    private function createDomainMessageForEvent($event, DateTime $occurredOn = null)
    {
        $this->playhead++;

        if ($occurredOn === null) {
            $occurredOn = DateTime::now();
        }

        return new DomainMessage($this->aggregateId, $this->playhead, new Metadata([]), $event, $occurredOn);
    }
}
