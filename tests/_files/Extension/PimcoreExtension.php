<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\Tests\Extension;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\DependencyFactory;
use Pimcore\Bundle\InstallBundle\Installer;
use Pimcore\Extension\Bundle\PimcoreBundleInterface;
use Pimcore\Extension\Bundle\PimcoreBundleManager;
use Pimcore\Model\DataObject\ClassDefinition\ClassDefinitionManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pimcore\Model\DataObject;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Valantic\ElasticaBridgeBundle\Tests\Traits\SymfonyKernelTrait;

final class PimcoreExtension
{
    use SymfonyKernelTrait;

    private const CLASSIFICATION_STORE_NAME = 'Default';

    public function executeBeforeFirstTest(): void
    {
        self::setUpPimcore();
        self::setUpPimcoreBundles();
        self::setUpPimcoreDataObjects();
        self::setUpClassificationStore();

        //        self::ensureKernelShutdown();
    }

    public function executeAfterLastTest(): void {}

    public function executeBeforeTest(): void {}

    private static function setUpPimcore(): void
    {
        /** @var Connection $connection */
        $connection = self::getContainer()->get('database_connection');
        $databaseName = $connection->getDatabase();
        if ($databaseName === null) {
            throw new \Exception('Database name is null');
        }

        // use a dedicated setup connection as the framework connection is bound to the DB and will
        // fail if the DB doesn't exist
        $setupConnection = DriverManager::getConnection($connection->getParams(), $connection->getConfiguration());
        $schemaManager = $setupConnection->createSchemaManager();

        $databases = $schemaManager->listDatabases();
        if (in_array($databaseName, $databases, true)) {
            $schemaManager->dropDatabase($connection->quoteIdentifier($databaseName));
        }

        $schemaManager->createDatabase($connection->quoteIdentifier($databaseName) . ' charset=utf8mb4');

        /** @var LoggerInterface $logger */
        $logger = self::getContainer()->get('monolog.logger.pimcore');

        /** @var EventDispatcherInterface $eventDispatcher */
        $eventDispatcher = self::getContainer()->get('event_dispatcher');

        $installer = new Installer($logger, $eventDispatcher);

        $installer->setImportDatabaseDataDump(false);
        $installer->setupDatabase($connection, [
            'username' => 'admin',
            'password' => 'admin',
        ]);

    }

    private static function setUpPimcoreDataObjects(): void
    {
        /** @var ClassDefinitionManager $classDefinitionManager */
        $classDefinitionManager = self::getContainer()->get(ClassDefinitionManager::class);
        $classDefinitionManager->createOrUpdateClassDefinitions();

        $list = new DataObject\Objectbrick\Definition\Listing();
        $list = $list->load();
        foreach ($list as $brickDefinition) {
            $brickDefinition->save(false);
        }

        $list = new DataObject\Fieldcollection\Definition\Listing();
        $list = $list->load();
        foreach ($list as $fc) {
            $fc->save(false);
        }

        $selectOptionConfigurations = new DataObject\SelectOptions\Config\Listing();
        foreach ($selectOptionConfigurations as $selectOptionConfiguration) {
            $selectOptionConfiguration->generateEnumFiles();
        }
    }

    private static function setUpPimcoreBundles(): void
    {
        $container = self::getContainer();

        $bundlesToInstall = self::getInstalledPimcoreBundles();

        if (count($bundlesToInstall) > 0) {
            /** @var PimcoreBundleManager $pimcoreBundleManager */
            $pimcoreBundleManager = self::getContainer()->get(PimcoreBundleManager::class);

            /** @var DependencyFactory $dependencyFactory */
            $dependencyFactory = $container->get(DependencyFactory::class);
            $dependencyFactory->getMetadataStorage()->ensureInitialized();

            foreach ($bundlesToInstall as $key => $bundleName) {
                /** TODO: fix installer */
                if ($key === 'PimcoreGoogleMarketingBundle') {
                    continue;
                }

                $bundle = $pimcoreBundleManager->getActiveBundle($bundleName::class, false);

                if ($pimcoreBundleManager->getInstaller($bundle) !== null) {
                    $pimcoreBundleManager->install($bundle);
                }
            }
        }
    }

    private static function setUpClassificationStore(): void
    {
        $storeConfig = new DataObject\Classificationstore\StoreConfig();
        $storeConfig->setName(self::CLASSIFICATION_STORE_NAME);
        $storeConfig->save();

        //        $groupConfig = new DataObject\Classificationstore\GroupConfig();
        //        $groupConfig->setStoreId($storeConfig->getId());
        //        $groupConfig->setName(self::CLASSIFICATION_GROUP_NAME);
        //        $groupConfig->save();
    }

    /**
     * @return PimcoreBundleInterface[]
     */
    protected static function getInstalledPimcoreBundles(): array
    {
        if (!self::$booted) {
            self::bootKernel();
        }

        return array_filter(
            self::$kernel->getBundles(),
            fn (BundleInterface $bundle) => ($bundle instanceof PimcoreBundleInterface),
        );
    }
}
