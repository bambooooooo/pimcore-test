<?php

namespace App\Model\Grid;

use Pimcore\Bundle\AdminBundle\DataObject\GridColumnConfig\Operator\AbstractOperator;
use Pimcore\Bundle\AdminBundle\DataObject\GridColumnConfig\ResultContainer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\Element\ElementInterface;

class Price extends AbstractOperator
{
    public function __construct(\stdClass $config, $context = null)
    {
        parent::__construct($config, $context);
        $this->offerId = (int)$config->additionalData;
    }

    public function getLabeledValue(array|ElementInterface $element): ResultContainer|\stdClass|null
    {
        $ret = new ResultContainer();

        if($element instanceof Product || $element instanceof ProductSet)
        {
            $price = null;

            foreach($element->getPrice() as $p)
            {
                if($p->getElement()->getId() == $this->offerId ?? 0)
                {
                    $price = $p->getPrice();
                }
            }

            $ret->setLabel("Price");
            $ret->setValue($price);
        }
        else
        {
            $ret->setLabel("Price");
            $ret->setValue("Object not supported");
        }

        return $ret;
    }
}

