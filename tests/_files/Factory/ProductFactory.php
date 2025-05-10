<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\Tests\Factory;

use Pimcore\Model\DataObject;

/** @extends AbstractDataObjectFactory<DataObject\Product> */
final class ProductFactory extends AbstractDataObjectFactory
{
    #[\Override]
    public static function class(): string
    {
        return DataObject\Product::class;
    }

    #[\Override]
    protected function defaults(): array|callable
    {
        $sku = self::faker()->unique()->uuid();

        return [
            'parent' => DataObject\Service::createFolderByPath('/products'),
            'key' => $sku,
            'sku' => $sku,
            'published' => true,
            'name' => [
                'en' => self::faker()->name(),
                'de' => self::faker()->name(),
            ],
        ];
    }
}
