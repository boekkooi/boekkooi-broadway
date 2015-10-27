<?php
namespace Boekkooi\Broadway\UuidGenerator\Testing;

use Boekkooi\Broadway\UuidGenerator\UuidGeneratorInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * Mock uuid generator that always generates the same id.
 */
class MockUuidGenerator implements UuidGeneratorInterface
{
    private $uuid;

    /**
     * @param Uuid $uuid
     */
    public function __construct(Uuid $uuid)
    {
        $this->uuid = $uuid;
    }

    /**
     * @inheritdoc
     */
    public function generate()
    {
        return $this->uuid;
    }
}
