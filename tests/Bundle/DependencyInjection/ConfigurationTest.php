<?php
namespace Tests\Boekkooi\Broadway\Bundle\DependencyInjection;

use Boekkooi\Broadway\Bundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    use ConfigurationTestCaseTrait;

    protected function getConfiguration()
    {
        return new Configuration();
    }

    public function testDefaults()
    {
        $this->assertProcessedConfigurationEquals(
            [],
            [
                'command_handling'=> [ 'command_bus' => 'default' ],
                'event_store' => [ 'serializer' => [ 'payload' => null, 'metadata' => null ] ],
                'read_model' => [ 'serializer' => null ],
            ]
        );
    }

    public function testCustoms()
    {
        $this->assertProcessedConfigurationEquals(
            [
                [ 'command_handling' => [ 'command_bus' => 'test' ] ],
                [ 'event_store' => [
                    'serializer' =>  [
                        'payload' => 'payloadSerializer',
                        'metadata' => 'metadataSerializer'
                    ]
                ] ],
                [ 'read_model' => [ 'serializer' => 'readModelSerializer' ] ],
            ],
            [
                'command_handling'=> [ 'command_bus' => 'test' ],
                'event_store' => [
                    'serializer' => [
                        'payload' => 'payloadSerializer',
                        'metadata' => 'metadataSerializer'
                    ]
                ],
                'read_model' => [ 'serializer' => 'readModelSerializer' ],
            ]
        );
    }

    public function testEventStoreSerializerNormalizer()
    {
        $this->assertProcessedConfigurationEquals(
            [
                [ 'event_store' => [
                    'serializer' =>  'eventStoreSerializer'
                ] ],
            ],
            [
                'event_store' => [
                    'serializer' => [
                        'payload' => 'eventStoreSerializer',
                        'metadata' => 'eventStoreSerializer'
                    ]
                ],
            ],
            'event_store'
        );
    }
}
