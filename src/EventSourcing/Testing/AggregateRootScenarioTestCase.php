<?php
namespace Boekkooi\Broadway\EventSourcing\Testing;

use Broadway\EventSourcing\Testing\AggregateRootScenarioTestCase as TestCase;

abstract class AggregateRootScenarioTestCase extends TestCase
{
    /**
     * @return Scenario
     */
    protected function createScenario()
    {
        $aggregateRootClass = $this->getAggregateRootClass();
        $factory            = $this->getAggregateRootFactory();

        return new Scenario($this, $factory, $aggregateRootClass);
    }
}
