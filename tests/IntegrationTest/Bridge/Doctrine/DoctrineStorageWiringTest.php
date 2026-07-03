<?php

namespace Locastic\Loggastic\Tests\IntegrationTest\Bridge\Doctrine;

use Locastic\Loggastic\Bridge\Doctrine\Storage\DoctrineActivityLogStorage;
use Locastic\Loggastic\Bridge\Doctrine\Storage\DoctrineCurrentDataTrackerStorage;
use Locastic\Loggastic\Bridge\Doctrine\Storage\DoctrineStorageInitializer;
use Locastic\Loggastic\Bridge\Elasticsearch\ElasticsearchService;
use Locastic\Loggastic\Storage\ActivityLogStorageInterface;
use Locastic\Loggastic\Storage\CurrentDataTrackerStorageInterface;
use Locastic\Loggastic\Storage\StorageInitializerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Boots the fixture app with storage: doctrine and checks the storage interfaces
 * resolve to the Doctrine bridge while no Elasticsearch service gets registered.
 */
class DoctrineStorageWiringTest extends KernelTestCase
{
    protected function setUp(): void
    {
        $_SERVER['APP_ENV'] = 'doctrine_storage';

        self::bootKernel(['environment' => 'doctrine_storage']);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $_SERVER['APP_ENV'] = 'test';
    }

    public function testStorageInterfacesAreAliasedToDoctrineBridge(): void
    {
        $container = self::getContainer();

        self::assertInstanceOf(DoctrineActivityLogStorage::class, $container->get(ActivityLogStorageInterface::class));
        self::assertInstanceOf(DoctrineCurrentDataTrackerStorage::class, $container->get(CurrentDataTrackerStorageInterface::class));
        self::assertInstanceOf(DoctrineStorageInitializer::class, $container->get(StorageInitializerInterface::class));
    }

    public function testElasticsearchServicesAreNotRegistered(): void
    {
        self::assertFalse(self::getContainer()->has(ElasticsearchService::class));
    }
}
