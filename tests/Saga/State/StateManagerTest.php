<?php

namespace Tests\Boekkooi\Broadway\Saga\State;

use Boekkooi\Broadway\Saga\State\StateManager;
use Boekkooi\Broadway\UuidGenerator\Testing\MockUuidGenerator;
use Broadway\Saga\State;
use Broadway\Saga\State\Criteria;
use Broadway\Saga\State\InMemoryRepository;
use Broadway\TestCase;
use Rhumsaa\Uuid\Uuid;

class StateManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Broadway\Saga\State\RepositoryInterface
     */
    private $repository;
    /**
     * @var \Boekkooi\Broadway\UuidGenerator\UuidGeneratorInterface
     */
    private $generator;
    /**
     * @var \Broadway\Saga\State\StateManagerInterface
     */
    private $manager;

    public function setUp()
    {
        $this->repository = new InMemoryRepository();
        $this->generator  = new MockUuidGenerator(Uuid::fromString('e2d0c739-0001-434c-8d7a-03e29b400566'));
        $this->manager    = new StateManager($this->repository, $this->generator);
    }

    /**
     * @test
     */
    public function it_returns_a_new_state_object_if_the_criteria_is_null()
    {
        $state = $this->manager->findOneBy(null, 'sagaId');

        self::assertEquals(
            new State(Uuid::fromString('e2d0c739-0001-434c-8d7a-03e29b400566')),
            $state
        );
    }

    /**
     * @test
     */
    public function it_returns_an_existing_state_instance_matching_the_returned_criteria()
    {
        $state = new State(1337);
        $state->set('appId', 1337);
        $this->repository->save($state, 'sagaId');
        $criteria = new Criteria(array('appId' => 1337));

        $resolvedState = $this->manager->findOneBy($criteria, 'sagaId');

        self::assertEquals($state, $resolvedState);
    }

    /**
     * @test
     */
    public function it_returns_null_when_repository_does_not_find_for_given_criteria()
    {
        $criteria = new Criteria(array('appId' => 1337));

        $resolvedState = $this->manager->findOneBy($criteria, 'sagaId');

        self::assertNull($resolvedState);
    }
}
