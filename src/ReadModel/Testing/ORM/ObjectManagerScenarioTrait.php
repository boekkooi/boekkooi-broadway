<?php
namespace Boekkooi\Broadway\ReadModel\Testing\ORM;

use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\Setup;

trait ObjectManagerScenarioTrait
{
    /**
     * Creates a new entity manager.
     *
     * @return ObjectManager
     */
    protected function createObjectManager()
    {
        $entityManager = EntityManager::create(
            [
                'driver'   => 'pdo_sqlite',
                'memory'   => true
            ],
            $this->createOrmConfiguration()
        );

        $this->createSchemaForSupportedEntities($entityManager);

        return $entityManager;
    }

    /**
     * Creates the schema for the managed entities.
     *
     * @param EntityManagerInterface $entityManager
     */
    protected function createSchemaForSupportedEntities(EntityManagerInterface $entityManager)
    {
        $entityClasses = $this->getEntityClasses();
        $entityClasses[] = $this->getRepositoryObjectClass();

        $metadata = array_map(function ($className) use ($entityManager) {
            return $entityManager->getMetadataFactory()->getMetadataFor($className);
        }, $entityClasses);

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->createSchema($metadata);
    }

    /**
     * Create a orm configuration
     *
     * @return Configuration
     */
    protected function createOrmConfiguration()
    {
        $config = Setup::createConfiguration(
            true,
            null,
            new ArrayCache()
        );

        $config->setMetadataDriverImpl(
            $this->createOrmMappingDriver($config)
        );

        return $config;
    }

    /**
     * Create a mapping driver.
     *
     * @param Configuration $configuration
     * @return \Doctrine\Common\Persistence\Mapping\Driver\MappingDriver
     */
    protected function createOrmMappingDriver(Configuration $configuration)
    {
        $entityClasses = $this->getEntityClasses();
        $entityClasses[] = $this->getRepositoryObjectClass();

        $entityPaths = array_unique(array_map(
            function ($className) {
                $info = new \ReflectionClass($className);
                return dirname($info->getFileName());
            },
            $entityClasses
        ));

        return new StrictEntityDriverDecorator(
            $configuration->newDefaultAnnotationDriver($entityPaths, false),
            $entityClasses
        );
    }

    /**
     * Returns the object classes used for the scenario repository.
     *
     * @return string
     */
    abstract protected function getRepositoryObjectClass();

    /**
     * Returns a list of entity classes that must be know by the EntityManager.
     *
     * @return string[]
     */
    protected function getEntityClasses()
    {
        return [];
    }
}
