<?php
namespace Boekkooi\Broadway\ReadModel\Testing;

use Boekkooi\Broadway\Testing\Comparator\DoctrineCollectionComparator;
use Broadway\EventHandling\EventListenerInterface;
use Doctrine\Common\Persistence\ObjectRepository;
use SebastianBergmann\Comparator\Factory;

abstract class EventListenerScenarioTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Scenario
     */
    protected $scenario;

    /**
     * @var \SebastianBergmann\Comparator\Comparator[]
     */
    private static $comparators = null;

    public static function setUpBeforeClass()
    {
        if (self::$comparators === null) {
            self::$comparators = array(
                new DoctrineCollectionComparator()
            );
            array_map(array(Factory::getInstance(), 'register'), self::$comparators);
        }

        parent::setUpBeforeClass();
    }

    public static function tearDownAfterClass()
    {
        if (is_array(self::$comparators)) {
            array_map(array(Factory::getInstance(), 'unregister'), self::$comparators);
            self::$comparators = null;
        }

        parent::tearDownAfterClass();
    }

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
    abstract protected function getRepository();

    /**
     * Returns the event listener to test.
     *
     * @return EventListenerInterface
     */
    abstract protected function createEventListener();
}
