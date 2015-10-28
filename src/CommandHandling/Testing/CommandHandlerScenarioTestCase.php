<?php
namespace Boekkooi\Broadway\CommandHandling\Testing;

use Broadway\CommandHandling\Testing\Scenario;
use Broadway\EventHandling\EventBusInterface;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventSourcing\AggregateFactory\PublicConstructorAggregateFactory;
use Broadway\EventSourcing\EventSourcingRepository;
use Broadway\EventStore\EventStoreInterface;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\EventStore\TraceableEventStore;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use League\Tactician\Handler\CommandHandlerMiddleware;
use League\Tactician\Handler\CommandNameExtractor\ClassNameExtractor;
use League\Tactician\Handler\Locator\CallableLocator;
use League\Tactician\Handler\MethodNameInflector\HandleClassNameInflector;

abstract class CommandHandlerScenarioTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UuidGeneratorInterface
     */
    protected $generator;
    /**
     * @var Scenario
     */
    protected $scenario;

    protected function setUp()
    {
        $this->generator = $this->createGenerator();

        $this->scenario = $this->createScenario();
    }

    /**
     * @return Scenario
     */
    protected function createScenario()
    {
        $eventStore     = new TraceableEventStore(new InMemoryEventStore());
        $eventBus       = new SimpleEventBus();

        $commandHandler = $this->createCommandHandler($eventStore, $eventBus);
        $commandHandlerMiddleware = $this->createCommandHandlerMiddleware($commandHandler);

        return new Scenario($this, $eventStore, new CommandHandlerToMiddleware($commandHandlerMiddleware));
    }

    /**
     * @return UuidGeneratorInterface
     */
    protected function createGenerator()
    {
        return new Version4Generator();
    }

    /**
     * Returns the command handler middleware
     *
     * @param object $commandHandler
     * @return CommandHandlerMiddleware
     */
    protected function createCommandHandlerMiddleware($commandHandler)
    {
        return new CommandHandlerMiddleware(
            new ClassNameExtractor(),
            new CallableLocator(function () use ($commandHandler) { return $commandHandler; }),
            new HandleClassNameInflector()
        );
    }

    /**
     * Returns a factory for instantiating an aggregate
     *
     * @return \Broadway\EventSourcing\AggregateFactory\AggregateFactoryInterface $factory
     */
    protected function getAggregateRootFactory()
    {
        return new PublicConstructorAggregateFactory();
    }

    /**
     * Returns a repository for a specific aggregate
     *
     * @param EventStoreInterface $eventStore
     * @param EventBusInterface $eventBus
     * @param string $aggregateClass
     * @return \Broadway\Repository\RepositoryInterface $factory
     */
    protected function createEventSourcingRepository(EventStoreInterface $eventStore, EventBusInterface $eventBus, $aggregateClass)
    {
        return new EventSourcingRepository($eventStore, $eventBus, $aggregateClass, $this->getAggregateRootFactory());
    }

    /**
     * Create a command handler for the given scenario test case.
     *
     * @param EventStoreInterface $eventStore
     * @param EventBusInterface   $eventBus
     *
     * @return object
     */
    abstract protected function createCommandHandler(EventStoreInterface $eventStore, EventBusInterface $eventBus);
}
