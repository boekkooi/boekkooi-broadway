<?php
namespace Tests\Boekkooi\Broadway\Bundle\DependencyInjection\Compiler;

use Boekkooi\Broadway\Bundle\DependencyInjection\Compiler\EventHandlerMapPass;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractCompilerPassTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Tests\Boekkooi\Broadway\Fixtures\EventHandler\ExampleEventHandler;

class EventHandlerMapPassTest extends AbstractCompilerPassTestCase
{
    protected function registerCompilerPass(ContainerBuilder $container)
    {
        $container->addCompilerPass(new EventHandlerMapPass());
    }

    public function testNothingIsDoneWhenLocatorIsNotDefined()
    {
        $collectedService = new Definition(ExampleEventHandler::class);
        $collectedService->addTag('broadway.domain.event_handler');
        $this->setDefinition('my_event_handler', $collectedService);

        $this->compile();

        $this->assertContainerBuilderNotHasService('boekkooi.broadway.event.handler.locator');
    }

    public function testNothingIsDoneWhenHandlerIsNotTagged()
    {
        $locatorService = new Definition();
        $this->setDefinition('boekkooi.broadway.event.handler.locator', $locatorService);

        $collectedService = new Definition(ExampleEventHandler::class);
        $this->setDefinition('my_event_handler', $collectedService);

        $this->compile();

        self::assertCount(0, $this->container->getDefinition('boekkooi.broadway.event.handler.locator')->getMethodCalls());
    }

    public function testEventHandlerEventsAreRegisteredWithTheLocator()
    {
        $locatorService = new Definition();
        $this->setDefinition('boekkooi.broadway.event.handler.locator', $locatorService);

        $collectedService = new Definition(ExampleEventHandler::class);
        $collectedService->addTag('broadway.domain.event_handler');
        $this->setDefinition('my_event_handler', $collectedService);

        $this->compile();

        $this->assertContainerBuilderHasServiceDefinitionWithMethodCall(
            'boekkooi.broadway.event.handler.locator',
            'registerEventHandlerService',
            array(
                'my_event_handler',
                [ 'FinishedEvent', 'TestEvent' ]
            )
        );
    }
}
