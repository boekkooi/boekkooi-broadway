<?php
namespace Boekkooi\Broadway\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class EventHandlerMapPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('boekkooi.broadway.event_handler.locator')) {
            return;
        }

        $def = $container->getDefinition('boekkooi.broadway.event_handler.locator');
        foreach ($container->findTaggedServiceIds('boekkooi.domain.event_handler') as $id => $attributes) {
            $events = $this->extractEventNames(
                $container->getDefinition($id)
            );

            $def->addMethodCall(
                'registerEventHandlerService',
                [ $id, $events ]
            );
        }
    }

    private function extractEventNames(Definition $handlerDefinition)
    {
        $class = new \ReflectionClass($handlerDefinition->getClass());
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        $events = [];
        foreach ($methods as $method) {
            if ($method->isStatic() || strpos($method->getName(), 'handle') !== 0) {
                continue;
            }

            $events[] = substr($method->getName(), 6);
        }

        return $events;
    }
}
