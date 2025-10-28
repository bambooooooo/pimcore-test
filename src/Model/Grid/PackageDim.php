<?php

namespace App\Model\Grid;

use Pimcore\Bundle\AdminBundle\DataObject\GridColumnConfig\Operator\AbstractOperator;
use Pimcore\Bundle\AdminBundle\DataObject\GridColumnConfig\ResultContainer;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\Element\ElementInterface;

class PackageDim extends AbstractOperator
{
    public function __construct(\stdClass $config, $context = null)
    {
        parent::__construct($config, $context);

        $data = json_decode($config->additionalData);

        $this->n = intval($data->n) ?? null;
        $this->d = $data->d ?? null;
    }

    public function getLabeledValue(array|ElementInterface $element): ResultContainer|\stdClass|null
    {
        $ret = new ResultContainer();

        $ret->setLabel("Dim");

        if(!$this->n || !$this->d)
            return $ret;

        $dimension = $this->d;
        $method = "get{$dimension}";

        if(!method_exists(Package::class, $method))
        {
            return $ret;
        }

        if($element instanceof Product)
        {
            $nth = 1;
            foreach ($element->getPackages() as $lip)
            {
                /** @var Package $package */
                $package = $lip->getElement();

                for($i=0; $i<$lip->getQuantity(); $i++)
                {
                    if($nth == $this->n)
                    {
                        $value = $package->{$method}();

                        if($value instanceof QuantityValue)
                        {
                            $value = $value->getValue();
                        }

                        $ret->setValue($value);
                        return $ret;
                    }

                    $nth++;
                }
            }
        }
        else if($element instanceof ProductSet)
        {
            $nth = 1;
            foreach ($element->getSet() as $lis) {

                /** @var Product $product */
                $product = $lis->getElement();

                for($i=0; $i<$lis->getQuantity(); $i++)
                {
                    foreach ($product->getPackages() as $lip)
                    {
                        /** @var Package $package */
                        $package = $lip->getElement();

                        for($j=0; $j<$lip->getQuantity(); $j++)
                        {
                            if ($nth == $this->n) {
                                $value = $package->{$method}();

                                if ($value instanceof QuantityValue) {
                                    $value = $value->getValue();
                                }

                                $ret->setValue($value);
                                return $ret;
                            }

                            $nth++;
                        }
                    }
                }
            }
        }
        else
        {
            $ret->setValue("Object not supported");
        }

        return $ret;
    }
}

