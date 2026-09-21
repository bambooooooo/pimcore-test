<?php

namespace App\OptionProvider;

use App\Model\AdminStyle\Product;
use Pimcore\Model\DataObject\ClassDefinition;
use Pimcore\Model\DataObject\ClassDefinition\Data;
use Pimcore\Model\DataObject\ClassDefinition\Data\OptionsProviderInterface;
use Pimcore\Model\DataObject\ClassDefinition\DynamicOptionsProvider\SelectOptionsProviderInterface;


class PriceLevelProvider implements SelectOptionsProviderInterface
{
    public function getOptions(array $context, Data $fieldDefinition): array
    {
        $fields = ClassDefinition::getById("Product");

        $prefix = 'price_';
        if($fieldDefinition && strlen($fieldDefinition->getOptionsProviderData()))
        {
            $prefix = $fieldDefinition->getOptionsProviderData();
        }

        $ret = [];

        foreach ($fields->getFieldDefinitions() as $field) {
            if(str_starts_with($field->getName(), $prefix) && $field->getFieldType() == 'quantityValue')
            {
                $ret[] = [
                    'key' => $field->getTitle(),
                    'value' => $field->getName(),
                ];
            }
        }

        usort($ret, function ($a, $b) {
            return strcmp($a["key"], $b["key"]);
        });

        return $ret;
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
