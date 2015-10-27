<?php
namespace Boekkooi\Broadway\Testing;

use Broadway\CommandHandling\CommandHandlerInterface;
use League\Tactician\Middleware;

class CommandHandlerToMiddleware implements CommandHandlerInterface
{
    /**
     * @var Middleware
     */
    private $commandMiddleware;

    public function __construct(Middleware $commandMiddleware)
    {
        $this->commandMiddleware = $commandMiddleware;
    }

    /**
     * @param mixed $command
     */
    public function handle($command)
    {
        $this->commandMiddleware->execute($command, function () { });
    }
}
