<?php

declare(strict_types=1);

namespace App\Elasticsearch\Index\Product;

use Pimcore\Model\DataObject;
use Pimcore\Model\Element\AbstractElement;
use Valantic\ElasticaBridgeBundle\Document\AbstractTenantAwareDocument;
use Valantic\ElasticaBridgeBundle\Document\DataObjectNormalizerTrait;
use Valantic\ElasticaBridgeBundle\Enum\DocumentType;

/**
 * @extends AbstractTenantAwareDocument<DataObject\Product>
 */
class ProductIndexDocument extends AbstractTenantAwareDocument
{
    /** @use DataObjectNormalizerTrait<DataObject\Product> */
    use DataObjectNormalizerTrait;

    public function getType(): DocumentType
    {
        return DocumentType::DATA_OBJECT;
    }

    public function getSubType(): ?string
    {
        return DataObject\Product::class;
    }

    public function getNormalized(AbstractElement $element): array
    {
        return [
            ...$this->plainAttributes(
                $element,
                [
                    'sku',
                    'created_at' => fn (DataObject\Product $product) => date('r', $product->getCreationDate()),
                ]
            ),
            ...$this->localizedAttributes(
                $element,
                [
                    'name',
                ]
            ),
//            ...$this->relationAttributes(
//                $element,
//                [
//                    ProductIndex::ATTRIBUTE_CATEGORIES => 'categories',
//                    'relatedProducts' => fn (DataObject\Product $product) => $product->getRelatedProducts(),
//                ]
//            ),
//            ...$this->children($element),
//            ...$this->childrenRecursive($element),
        ];
    }

    public function shouldIndex(AbstractElement $element): bool
    {
        return $element->isPublished();
    }
}
