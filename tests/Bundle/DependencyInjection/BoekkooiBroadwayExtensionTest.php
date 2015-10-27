<?php
namespace Tests\Boekkooi\Broadway\Bundle\DependencyInjection;

use Boekkooi\Broadway\Bundle\DependencyInjection\BoekkooiBroadwayExtension;
use Boekkooi\Broadway\EventHandling\HandlerEventListener;
use Boekkooi\Broadway\Saga\State\StateManager;
use Boekkooi\Broadway\UuidGenerator\Rfc4122\Version4Generator;
use Broadway\Bundle\BroadwayBundle\DependencyInjection\BroadwayExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\ContainerBuilderHasAliasConstraint;

class BoekkooiBroadwayExtensionTest extends AbstractExtensionTestCase
{
    protected function getContainerExtensions()
    {
        return array(
            new BroadwayExtension(),
            new BoekkooiBroadwayExtension()
        );
    }

    /**
     * @test
     */
    public function after_loading_the_correct_parameter_has_been_set()
    {
        $this->load();

        $this->assertContainerBuilderHasAlias('broadway.command_handling.command_bus', 'tactician.commandbus.default');
        $this->assertContainerBuilderHasService('broadway.uuid.generator', Version4Generator::class);
        $this->assertContainerBuilderHasService('broadway.saga.state.state_manager', StateManager::class);

        $this->assertContainerBuilderHasService('boekkooi.broadway.event_handler.listener', HandlerEventListener::class);

        $this->assertContainerBuilderHasNoAlias('broadway.serializer.payload');
        $this->assertContainerBuilderHasNoAlias('broadway.serializer.metadata');
        $this->assertContainerBuilderHasNoAlias('broadway.serializer.readmodel');
    }

    /**
     * @test
     */
    public function when_custom_serializer_services_are_set_they_persist()
    {
        $boekkooiConfig = [
            'event_store' => [
                'serializer' => [
                    'payload' => 'my.payload.serializer',
                    'metadata' => 'my.metadata.serializer'
                ]
            ],
            'read_model' => [
                'serializer' => 'my.read_model.serializer'
            ]
        ];

        foreach ($this->container->getExtensions() as $extension) {
            $config = $this->getMinimalConfiguration();
            if ($extension instanceof BoekkooiBroadwayExtension) {
                $config = [ $boekkooiConfig ];
            }
            $extension->load($config, $this->container);
        }

        $this->assertContainerBuilderHasAlias('broadway.serializer.payload', 'my.payload.serializer');
        $this->assertContainerBuilderHasAlias('broadway.serializer.metadata', 'my.metadata.serializer');
        $this->assertContainerBuilderHasAlias('broadway.serializer.readmodel', 'my.read_model.serializer');
   }

    public function assertContainerBuilderHasNoAlias($aliasId)
    {
        self::assertThat(
            $this->container,
            new \PHPUnit_Framework_Constraint_Not(
                new ContainerBuilderHasAliasConstraint($aliasId)
            )
        );
    }
}
