<?php
namespace Boekkooi\Broadway\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BoekkooiBroadwayExtension extends Extension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));

        $configuration = $this->getConfiguration($configs, $container);
        $config        = $this->processConfiguration($configuration, $configs);

        $loader->load('broadway_overrides.yml');
        $loader->load('event_handling.yml');

        $this->loadCommandBus($config['command_handling'], $container);
        $this->loadSerializers($config, $container);
    }

    private function loadCommandBus(array $config, ContainerBuilder $container)
    {
        $container->setAlias(
            'broadway.command_handling.command_bus',
            'tactician.commandbus.' . $config['command_bus']
        );

        // "broadway.serializer.payload
        // broadway.serializer.metadata
        // broadway.serializer.readmodel
    }

    private function loadSerializers(array $config, ContainerBuilder $container)
    {
        if ($config['event_store']['serializer']['payload'] !== null) {
            $container->setAlias(
                'broadway.serializer.payload',
                $config['event_store']['serializer']['payload']
            );
        }
        if ($config['event_store']['serializer']['metadata'] !== null) {
            $container->setAlias(
                'broadway.serializer.metadata',
                $config['event_store']['serializer']['metadata']
            );
        }
        if ($config['read_model']['serializer'] !== null) {
            $container->setAlias(
                'broadway.serializer.readmodel',
                $config['read_model']['serializer']
            );
        }
    }
}
