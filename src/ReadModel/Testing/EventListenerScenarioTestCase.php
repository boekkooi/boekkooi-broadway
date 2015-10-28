<?php
namespace Boekkooi\Broadway\ReadModel\Testing;

use Broadway\EventHandling\EventListenerInterface;
use Doctrine\Common\Persistence\ObjectRepository;

abstract class EventListenerScenarioTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Scenario
     */
    protected $scenario;

    protected function setUp()
    {
        $this->scenario = $this->createScenario();

        parent::setUp();
    }

    /**
     * @return Scenario
     */
    protected function createScenario()
    {
        return new Scenario(
            $this,
            $this->getRepository(),
            $this->createEventListener()
        );
    }

    /**
     * Returns the object repository to use.
     *
     * @return ObjectRepository
     */
    protected abstract function getRepository();

    /**
     * Returns the event listener to test.
     *
     * @return EventListenerInterface
     */
    protected abstract function createEventListener();
}
