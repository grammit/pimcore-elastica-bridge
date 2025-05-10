<?php

declare(strict_types=1);

namespace Valantic\ElasticaBridgeBundle\Tests\Factory;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Tool;
use Zenstruck\Foundry\ObjectFactory;

/**
 * @template TModel of object
 *
 * @template-extends ObjectFactory<TModel>
 */
abstract class AbstractDataObjectFactory extends ObjectFactory
{
    private ?array $localizedValues = null;

    protected function initialize(): static
    {
        $this->localizedValues = [];

        return $this
            ->beforeInstantiate(function(array $parameters, string $className) {
                /** @var DataObject\Concrete $refClass */
                $refClass = (new \ReflectionClass($className))->newInstance();

                if ($refClass->getClassId() === null) {
                    return $parameters;
                }

                $localizedFields = ClassDefinition::getById($refClass->getClassId())
                    ?->getFieldDefinition('localizedfields');

                if ($localizedFields instanceof ClassDefinition\Data\Localizedfields) {
                    $localizedFields = array_keys($localizedFields->getFieldDefinitions());

                    $this->localizedValues = array_filter(
                        $parameters,
                        fn ($key) => in_array($key, $localizedFields, true),
                        \ARRAY_FILTER_USE_KEY,
                    );

                    return array_filter(
                        $parameters,
                        fn ($key) => !in_array($key, $localizedFields, true),
                        \ARRAY_FILTER_USE_KEY,
                    );
                }

                return $parameters;
            })
            ->afterInstantiate(function($object): void {
                if (!$object instanceof DataObject\AbstractObject || empty($this->localizedValues)) {
                    return;
                }

                foreach ($this->localizedValues as $property => $params) {
                    if (is_array($params)) {
                        foreach ($params as $language => $value) {
                            if (Tool::isValidLanguage($language)) {
                                $object->set($property, $value, $language);
                            }
                        }
                    } else {
                        $object->set($property, $params);
                    }
                }
            })
            ->afterInstantiate(function($object): void {
                if ($object instanceof DataObject\AbstractObject) {
                    $object->save();
                }
            });
    }
}
