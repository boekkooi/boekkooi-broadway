<?php
namespace Boekkooi\Broadway\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * This compiler pass maps Handler DI tags to specific commands by auto detecting the command handler.
 */
class CommandHandlerMapPass implements CompilerPassInterface
{
    /**
     * You can modify the container here before it is dumped to PHP code.
     *
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     * @api
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('tactician.handler.locator.symfony')) {
            throw new \Exception('Missing tactician.handler.locator.symfony definition');
        }

        $handlerLocator = $container->findDefinition('tactician.handler.locator.symfony');

        if (count($handlerLocator->getArguments()) >= 2) {
            $mapping = $handlerLocator->getArgument(1);
        } else {
            $handlerLocator->addArgument([]);
            $mapping = [];
        }

        foreach ($container->findTaggedServiceIds('boekkooi.broadway.command_handler') as $id => $tags) {
            $handlerCommands = $this->extractCommands(
                $container->getDefinition($id)
            );

            foreach ($handlerCommands as $commandClass) {
                if (isset($mapping[$commandClass])) {
                    throw new \Exception(sprintf(
                        'The boekkooi.broadway.command_handler tag found a duplicate handler for %s both are handled by %s and %s',
                        $commandClass,
                        $mapping[$commandClass],
                        $id
                    ));
                }

                $mapping[$commandClass] = $id;
            }
        }

        $handlerLocator->replaceArgument(1, $mapping);
    }

    private function extractCommands(Definition $handlerDefinition)
    {
        $class = new \ReflectionClass($handlerDefinition->getClass());
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        $commands = [];
        foreach ($methods as $method) {
            if ($method->isStatic() || strpos($method->getName(), 'handle') !== 0) {
                continue;
            }

            $parameters = $method->getParameters();
            if (count($parameters) !== 1 || $parameters[0]->getClass() === null) {
                continue;
            }


            $commands[] = $parameters[0]->getClass()->name;
        }

        return $commands;
    }
}

