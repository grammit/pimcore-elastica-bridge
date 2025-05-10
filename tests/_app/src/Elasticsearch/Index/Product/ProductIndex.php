<?php

declare(strict_types=1);

namespace App\Elasticsearch\Index\Product;

use Elastica\Query\BoolQuery;
use Elastica\Query\MatchQuery;
use Elastica\Query\MultiMatch;
use Pimcore\Model\DataObject;
use Valantic\ElasticaBridgeBundle\Document\DocumentInterface;
use Valantic\ElasticaBridgeBundle\Enum\DocumentType;
use Valantic\ElasticaBridgeBundle\Index\AbstractIndex;

//class ProductIndex extends AbstractTenantAwareIndex
class ProductIndex extends AbstractIndex
{
    public const string ATTRIBUTE_CATEGORIES = 'categories';

    public function getName(): string
    {
        return 'product';
    }

    public function getAllowedDocuments(): array
    {
        return [
            ProductIndexDocument::class,
        ];
    }

    public function filterByLocaleAndQuery(string $locale, string $query): BoolQuery
    {
        return (new BoolQuery())
            ->addMust(
                (new MultiMatch())
                    ->setFields([
                        sprintf('%s.%s.*', DocumentInterface::ATTRIBUTE_LOCALIZED, $locale),
                    ])
                    ->setQuery($query)
            )
            ->addFilter(new MatchQuery(DocumentInterface::META_TYPE, DocumentType::DATA_OBJECT->value))
            ->addFilter(new MatchQuery(DocumentInterface::META_SUB_TYPE, DataObject\Product::class));
    }

//    public function getTenantUnawareName(): string
//    {
//        return 'product';
//    }
//
//    public function getTenants(): array
//    {
//        return ['acme'];
//    }
//
//    public function getDefaultTenant(): string
//    {
//        return 'acme';
//    }

}
