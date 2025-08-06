<?php

namespace App\OptionProvider;

use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\SelectOptionsProviderInterface;
use Pimcore\Model\DataObject\Classificationstore;
use Pimcore\Model\DataObject\Classificationstore\KeyConfig;

class ClassificationStoreKeysProvider implements SelectOptionsProviderInterface
{

    public function getOptions(array $context, Data $fieldDefinition): array
    {
        $storeName = (string)$fieldDefinition->getOptionsProviderData();
        if(!$storeName)
        {
            return [];
        }

        $store = Classificationstore\StoreConfig::getByName($storeName);
        if(!$store)
        {
            return [];
        }

        $storeId = $store->getId();

        $groups = new Classificationstore\GroupConfig\Listing();
        $groups->setCondition("storeId = $storeId");
        $groups = $groups->load();

        $output = [];

        foreach ($groups as $group)
        {
            foreach ($group->getRelations() as $groupKey)
            {
                $key = KeyConfig::getById($groupKey->getKeyId());

                $output[] = [
                    "key" => $key->getTitle() . " [" . $groupKey->getGroupId() . "~" . $key->getId() . "]",
                    "value" => $groupKey->getGroupId() . "~" . $key->getId()
                ];
            }
        }

        return $output;
    }

    public function hasStaticOptions(array $context, Data $fieldDefinition): bool
    {
        return true;
    }

    public function getDefaultValue(array $context, Data $fieldDefinition): string|array|null
    {
        return null;
    }
}
