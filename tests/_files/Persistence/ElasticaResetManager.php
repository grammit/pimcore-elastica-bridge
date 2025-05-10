<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\Tests\Persistence;

final class ElasticaResetManager
{
    public static function cleanUpOpenSearch(callable $createKernel): void
    {
        $kernel = $createKernel();

        // TODO:
    }

    //    protected static function initializeAndCleanupElasticSearch(): void
    //    {
    //        if (!self::pimcoreBundleIsInstalled('ValanticElasticaBridgeBundle')) {
    //            return;
    //        }
    //
    //        $esClient = static::getContainer()->get(ElasticsearchClient::class);
    //        $indexRepository = static::getContainer()->get(IndexRepository::class);
    //
    //        foreach ($indexRepository->flattenedAll() as $indexConfig) {
    //
    //            $index = $esClient->getIndex($indexConfig->getName());
    //            $currentIndex = $index;
    //
    //            static::ensureCorrectIndexSetup($indexConfig);
    //
    //            if ($indexConfig->usesBlueGreenIndices()) {
    //                $currentIndex = $indexConfig->getBlueGreenInactiveElasticaIndex();
    //            }
    //
    //            if ($indexConfig->usesBlueGreenIndices()) {
    //                $currentIndex->delete();
    //                $currentIndex->create($indexConfig->getCreateArguments());
    //            }
    //
    //            $currentIndex->refresh();
    //        }
    //    }
    //
    //    private static function ensureCorrectIndexSetup(IndexInterface $indexConfig): void
    //    {
    //        $esClient = static::getContainer()->get(ElasticsearchClient::class);
    //
    //        $nonAliasIndex = $esClient->getIndex($indexConfig->getName());
    //
    //        if (
    //            $nonAliasIndex->exists()
    //            && !ElasticsearchResponse::getResponse($esClient->indices()->existsAlias(['name' => $indexConfig->getName()]))->asBool()
    //        ) {
    //            $nonAliasIndex->delete();
    //        }
    //
    //        foreach (IndexBlueGreenSuffix::cases() as $suffix) {
    //            $name = $indexConfig->getName() . $suffix->value;
    //            $aliasIndex = $esClient->getIndex($name);
    //
    //            if ($aliasIndex->exists()) {
    //                $aliasIndex->delete();
    //            }
    //
    //            if (!$aliasIndex->exists()) {
    //                $aliasIndex->create($indexConfig->getCreateArguments());
    //            }
    //        }
    //
    //        try {
    //            $indexConfig->getBlueGreenActiveSuffix();
    //        } catch (BlueGreenIndicesIncorrectlySetupException) {
    //            $esClient->getIndex($indexConfig->getName() . IndexBlueGreenSuffix::BLUE->value)
    //                ->addAlias($indexConfig->getName());
    //        }
    //    }

}
