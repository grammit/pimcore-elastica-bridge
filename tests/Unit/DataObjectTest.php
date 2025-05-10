<?php

namespace Valantic\ElasticaBridgeBundle\Tests\Unit;

use Valantic\ElasticaBridgeBundle\Messenger\Message\RefreshElement;
use Valantic\ElasticaBridgeBundle\Tests\Factory\ProductFactory;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class DataObjectTest extends TestCase
{
    use InteractsWithMessenger;

    public function testObjectsQueuedAfterSave(): void
    {
        ProductFactory::createMany(3);

        $this->transport('elastica_bridge_index')
            ->queue()
            ->assertContains(RefreshElement::class, 3);
    }

    public function testProcessQueue(): void
    {
        ProductFactory::createMany(3);

        $transport = $this->transport('elastica_bridge_index');
        $transport->queue()->assertContains(RefreshElement::class, 3);
        $transport->process();

    }
}
