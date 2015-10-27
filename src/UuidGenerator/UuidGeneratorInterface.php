<?php
namespace Boekkooi\Broadway\UuidGenerator;

use Rhumsaa\Uuid\Uuid;

/**
 * Generates uuids.
 */
interface UuidGeneratorInterface
{
    /**
     * @return Uuid
     */
    public function generate();
}
