<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\Tests\Persistence;

use Pimcore\Cache;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Document;
use Pimcore\Bundle\SeoBundle\Model\Redirect;
use Pimcore\Model\Element;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

final class PimcoreResetManager
{
    public static function cleanUpDatabase(callable $createKernel): void
    {
        $kernel = $createKernel();

        \Pimcore::collectGarbage();

        self::cleanUpTree(DataObject::getById(1));

        self::cleanUpTree(Asset::getById(1));

        self::cleanUpTree(Document::getById(1));

        self::cleanUpClassificationStores();

        self::cleanUpRedirects($kernel);

        // TODO: delete messenger_messages
        // TODO: delete uuid table

        \Pimcore::collectGarbage();
        Cache\RuntimeCache::clear();
        Cache::clearAll();
    }

    protected static function cleanUpTree(?Element\AbstractElement $root): void
    {
        if ($root === null) {
            return;
        }

        $children = null;
        if ($root instanceof DataObject\AbstractObject) {
            $children = $root->getChildren([], true);
        } elseif ($root instanceof Document) {
            $children = $root->getChildren(true);
        } elseif ($root instanceof Asset) {
            $children = $root->getChildren();
        }

        foreach ($children?->getData() ?? [] as $child) {
            $child->delete();
        }
    }

    protected static function cleanUpClassificationStores(?string $name = null): void
    {
        $listing = new DataObject\Classificationstore\StoreConfig\Listing();

        if (!empty($name)) {
            $listing->setCondition('name = ?', [$name]);
        }

        foreach ($listing->getData() ?? [] as $store) {
            $store->delete();
        }
    }

    protected static function cleanUpRedirects(KernelInterface $kernel): void
    {
        $bundles = array_filter(
            $kernel->getBundles(),
            fn (BundleInterface $bundle) => is_a($bundle, 'PimcoreSeoBundle', true),
        );

        if (!empty($bundles)) {
            $redirects = new Redirect\Listing();
            foreach ($redirects->getRedirects() as $redirect) {
                $redirect->delete();
            }
        }
    }
}
