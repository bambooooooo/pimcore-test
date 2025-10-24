<?php

namespace App\Model\Grid;

use Pimcore\Bundle\AdminBundle\DataObject\GridColumnConfig\Operator\AbstractOperator;
use Pimcore\Bundle\AdminBundle\DataObject\GridColumnConfig\ResultContainer;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\Element\ElementInterface;
use stdClass;

class Round extends AbstractOperator
{
    private string $precision;
    public function __construct(stdClass $config, array $context = [])
    {
        parent::__construct($config, $context);

        $this->precision = (int)$config->additionalData ?? 0;
    }

    public function getLabeledValue(array|ElementInterface $element): ResultContainer|stdClass|null
    {
        $result = new stdClass();
        $result->label = $this->label;
        $result->value = null;

        $children = $this->getChildren();

        if ($children) {
            $newChildrenResult = [];
            $isArrayType = null;

            foreach ($children as $c) {
                $childResult = $c->getLabeledValue($element);
                $isArrayType = $childResult->isArrayType ?? false;

                $childValues = $childResult->value ?? null;
                if ($childValues && !is_array($childValues)) {
                    $childValues = [$childValues];
                }

                $newValue = null;

                if (is_array($childValues)) {
                    foreach ($childValues as $value) {
                        if (is_array($value)) {
                            $newSubValues = [];
                            foreach ($value as $subValue) {
                                $subValue = $this->format($subValue);
                                $newSubValues[] = $subValue;
                            }
                            $newValue = $newSubValues;
                        } else {
                            $newValue = $this->format($value);
                        }
                    }
                }

                $newChildrenResult[] = $newValue;
            }

            $result->isArrayType = $isArrayType;
            if ($isArrayType) {
                $result->value = $newChildrenResult;
            } else {
                $result->value = $newChildrenResult[0];
            }
        }

        return $result;
    }

    public function format(mixed $theValue): string
    {
        if(is_numeric($theValue)) {
            $data = doubleval($theValue);
            return round($data, $this->precision);
        }

        if($theValue instanceof QuantityValue && $theValue->getValue())
        {
            $data = doubleval($theValue->getValue());
            return round($data, $this->precision);
        }

        return "";
    }
}
