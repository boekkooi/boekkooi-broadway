<?php
namespace Boekkooi\Broadway\UuidGenerator\Testing;

use Boekkooi\Broadway\UuidGenerator\UuidGeneratorInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * Mock uuid generator that always generates a given sequence of uuids.
 */
class MockUuidSequenceGenerator implements UuidGeneratorInterface
{
    private $uuids;

    /**
     * @param Uuid[] $uuids
     */
    public function __construct(array $uuids)
    {
        $this->uuids = $uuids;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        if (count($this->uuids) === 0) {
            throw new \RuntimeException('No more uuids in sequence');
        }

        return array_shift($this->uuids);
    }
}
