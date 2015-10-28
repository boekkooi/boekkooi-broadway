<?php
namespace Boekkooi\Broadway\ReadModel\Testing\ORM;

use Boekkooi\Broadway\ReadModel\Testing\EventListenerScenarioTestCase as TestCase;
use Doctrine\Common\Persistence\ObjectManager;

abstract class EventListenerScenarioTestCase extends TestCase
{
    use ObjectManagerScenarioTrait;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        $this->objectManager = $this->createObjectManager();

        parent::setUp();
    }

    /**
     * @inheritdoc
     */
    protected function getRepository()
    {
        return $this->objectManager->getRepository($this->getRepositoryObjectClass());
    }
}
