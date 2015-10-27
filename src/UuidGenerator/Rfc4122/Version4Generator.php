<?php
namespace Boekkooi\Broadway\UuidGenerator\Rfc4122;

use Boekkooi\Broadway\UuidGenerator\UuidGeneratorInterface;
use Rhumsaa\Uuid\Uuid;

/**
 * Generates a version4 uuid as defined in RFC 4122.
 */
class Version4Generator implements UuidGeneratorInterface
{
    /**
     * @inheritdoc
     */
    public function generate()
    {
        return Uuid::uuid4();
    }
}
