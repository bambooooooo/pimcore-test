<?php

namespace App\Model\Grid;

use Pimcore\Bundle\AdminBundle\DataObject\GridColumnConfig\Operator\AbstractOperator;
use Pimcore\Bundle\AdminBundle\DataObject\GridColumnConfig\ResultContainer;
use Pimcore\Model\Element\ElementInterface;

class Constant extends AbstractOperator
{
    public function __construct(\stdClass $config, $context = null)
    {
        parent::__construct($config, $context);
        $this->value = (float)$config->additionalData;
    }

    public function getLabeledValue(array|ElementInterface $element): ResultContainer|\stdClass|null
    {
        $ret = new ResultContainer();

        $ret->setLabel("const");
        $ret->setValue($this->value);

        return $ret;
    }
}
