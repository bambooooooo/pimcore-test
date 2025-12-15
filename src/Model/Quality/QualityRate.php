<?php

namespace App\Model\Quality;

use Pimcore\Model\DataObject\ClassDefinition\CalculatorClassInterface;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Data\CalculatedValue;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\Service;

class QualityRate implements CalculatorClassInterface
{
    public function compute(Concrete $object, CalculatedValue $context): string
    {
        return $this->getCalculatedValueForEditMode($object, $context);
    }

    public function getCalculatedValueForEditMode(Concrete $object, CalculatedValue $context): string
    {
        if($object instanceof Product || $object instanceof ProductSet)
        {
            return Service::useInheritedValues(true, function() use ($object) {
                $qualityDescriptor = new Quality();
                $qualities = $qualityDescriptor->getQuality($object);

                $totalRequired = 0;
                $actualRequired = 0;
                $totalOptional = 0;
                $actualOptional = 0;

                foreach($qualities['required'] as $quality)
                {
                    $totalRequired += $quality['goal'];
                    $actualRequired += $quality['actual'];
                }

                foreach($qualities['optional'] as $quality)
                {
                    $totalOptional += $quality['goal'];
                    $actualOptional += $quality['actual'];
                }

                $total = $totalRequired + $totalOptional;

                if($total == 0)
                    return 0;

                $q = ($actualRequired + $actualOptional) * 100 /  $total;
                $q = intval($q);

                return $q;
            });
        }

        return "";
    }
}
