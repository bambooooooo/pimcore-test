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
        $storeId = (int)$fieldDefinition->getOptionsProviderData();
        if(!$storeId)
        {
            return [];
        }

        $store = Classificationstore\StoreConfig::getById($storeId);

        $groups = new Classificationstore\GroupConfig\Listing();
        $groups->setCondition("storeId = $storeId");
        $groups = $groups->load();

        if(!$store)
        {
            return [];
        }

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
