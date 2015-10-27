<?php
namespace Boekkooi\Broadway\Saga\Testing;

use Boekkooi\Broadway\Saga\State\StateManager;
use Boekkooi\Broadway\UuidGenerator\Rfc4122\Version4Generator;
use Broadway\CommandHandling\CommandBusInterface;
use Broadway\CommandHandling\Testing\TraceableCommandBus;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\Saga\Metadata\StaticallyConfiguredSagaMetadataFactory;
use Broadway\Saga\MultipleSagaManager;
use Broadway\Saga\SagaInterface;
use Broadway\Saga\State\InMemoryRepository;
use Broadway\Saga\Testing\Scenario;

abstract class SagaScenarioTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Scenario
     */
    protected $scenario;

    /**
     * Create the saga you want to test in this test case
     *
     * @param CommandBusInterface $commandBus
     * @return SagaInterface
     */
    abstract protected function createSaga(CommandBusInterface $commandBus);

    protected function setUp()
    {
        parent::setUp();

        $this->scenario = $this->createScenario();
    }

    protected function createScenario()
    {
        $traceableCommandBus = new TraceableCommandBus();
        $saga                = $this->createSaga($traceableCommandBus);
        $sagaStateRepository = new InMemoryRepository();
        $sagaManager         = new MultipleSagaManager(
            $sagaStateRepository,
            array($saga),
            new StateManager($sagaStateRepository, new Version4Generator()),
            new StaticallyConfiguredSagaMetadataFactory(),
            new EventDispatcher()
        );

        return new Scenario($this, $sagaManager, $traceableCommandBus);
    }
}
