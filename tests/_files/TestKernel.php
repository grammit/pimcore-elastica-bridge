<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\Tests;

use Pimcore\Bundle\AdminBundle\PimcoreAdminBundle;
use Pimcore\HttpKernel\BundleCollection\BundleCollection;
use Pimcore\Kernel as PimcoreKernel;
use Valantic\ElasticaBridgeBundle\ValanticElasticaBridgeBundle;
use Zenstruck\Foundry\ZenstruckFoundryBundle;
use Zenstruck\Messenger\Test\ZenstruckMessengerTestBundle;

class TestKernel extends PimcoreKernel
{
//    public function shutdown(): void
//    {
//        // skip shutdown of pimcore Kernel \Pimcore\Kernel::shutdown()
//        \Symfony\Component\HttpKernel\Kernel::shutdown();
//    }

    public function registerBundlesToCollection(BundleCollection $collection): void
    {
        $collection->addBundle(new PimcoreAdminBundle(), 60);
        $collection->addBundle(new ValanticElasticaBridgeBundle());
        $collection->addBundle(new ZenstruckFoundryBundle());
        $collection->addBundle(new ZenstruckMessengerTestBundle());
    }
}
