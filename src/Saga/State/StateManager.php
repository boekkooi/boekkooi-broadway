<?php
namespace Boekkooi\Broadway\Saga\State;

use Boekkooi\Broadway\UuidGenerator\UuidGeneratorInterface;
use Broadway\Saga\State;
use Broadway\Saga\State\Criteria;
use Broadway\Saga\State\RepositoryInterface;
use Broadway\Saga\State\StateManagerInterface;

class StateManager implements StateManagerInterface
{
    private $repository;
    private $generator;

    public function __construct(RepositoryInterface $repository, UuidGeneratorInterface $generator)
    {
        $this->repository = $repository;
        $this->generator  = $generator;
    }

    /**
     * {@inheritDoc}
     */
    public function findOneBy($criteria, $sagaId)
    {
        // TODO: Use CreationPolicy to determine whether and how a new state should be created
        if ($criteria instanceof Criteria) {
            return $this->repository->findOneBy($criteria, $sagaId);
        }

        return new State($this->generator->generate());
    }
}

