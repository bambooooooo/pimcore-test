<?php

namespace App\Publishing;

use PHPUnit\Framework\MockObject\Generator\NameAlreadyInUseException;
use Pimcore\Model\DataObject\Accessory;

class AccessoryEventListener
{
    public function preAdd(Accessory $accessory)
    {
        $existing = new Accessory\Listing();
        $existing->setUnpublished(true);
        $existing->setCondition('`key` = ?', $accessory->getKey());
        if(count($existing->loadIdList()))
        {
            throw new NameAlreadyInUseException($accessory->getKey());
        }
    }

}
