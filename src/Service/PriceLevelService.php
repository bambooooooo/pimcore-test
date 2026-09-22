<?php

namespace App\Service;

use App\OptionProvider\PriceLevelProvider;
use InvalidArgumentException;
use Pimcore\Model\DataObject\ClassDefinition;

class PriceLevelService
{
    public function __construct(private readonly PriceLevelProvider $productPriceLevelProvider)
    {

    }

    public function getPriceLevelValidDates(string $prefix = "date_price_")
    {
        $fields = ClassDefinition::getById("Product");

        $ret = [];

        foreach ($fields->getFieldDefinitions() as $field) {
            if(str_starts_with($field->getName(), $prefix) && $field->getFieldType() == 'date')
            {
                $ret[] = [
                    'key' => $field->getTitle(),
                    'value' => $field->getName(),
                ];
            }
        }

        return $ret;
    }

    /**
     * Returns price level list assigned to Product class
     *
     * @string $prefix
     * @param string $prefix
     * @return array
     * @throws \Exception
     */
    public function getPriceLevels(string $prefix = 'price_'): array
    {
        $def = new ClassDefinition\Data\Select();
        $def->setOptionsProviderData($prefix);

        $levels = $this->productPriceLevelProvider->getOptions([], $def);

        $ret = [];
        foreach ($levels as $item)
        {
            $ret[$item['value']] = $item['key'];
        }

        return $ret;
    }

    public function prettyRoundPrice(float $price): float
    {
        if ($price < 3) {
            return $this->ceilToStep($price, 0.1);
        } elseif ($price < 10) {
            return $this->ceilToStep($price, 0.5);
        } elseif ($price < 50) {
            return $this->ceilToStep($price, 1);
        } elseif ($price < 100) {
            return $this->ceilToStep($price, 5);
        } elseif ($price < 1_000) {
            return $this->ceilToStep($price, 10) - 1;
        } elseif ($price < 1_100) {
            return 1_099;
        } elseif ($price < 2_000) {
            return $this->ceilToStep($price, 50) - 1;
        } elseif ($price < 2_100) {
            return 2_099;
        } elseif ($price < 3_000) {
            return $this->ceilToStep($price, 50) - 1;
        } elseif ($price < 3_100) {
            return 3_099;
        }

        return $this->ceilToStep($price, 100) - 1;
    }

    private function ceilToStep(float $value, float $step): float
    {
        if ($step <= 0) {
            throw new InvalidArgumentException('Step must be greater than zero.');
        }

        return ceil($value / $step) * $step;
    }

}
