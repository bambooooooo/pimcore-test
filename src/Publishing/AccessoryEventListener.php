<?php

namespace App\Publishing;

use PHPUnit\Framework\MockObject\Generator\NameAlreadyInUseException;
use Pimcore\Model\DataObject\Accessory;

class AccessoryEventListener
{
    public function preAdd(Accessory $accessory)
    {
        if($this->isAccessoryKeyIsTaken($accessory->getKey()))
        {
            throw new NameAlreadyInUseException($accessory->getKey());
        }
    }

    public function preUpdate(Accessory $accessory)
    {
        if($this->isAccessoryKeyIsTaken($accessory->getKey(), $accessory->getId()))
        {
            throw new NameAlreadyInUseException($accessory->getKey());
        }
    }

    private function isAccessoryKeyIsTaken(string $key, int $accessoryId = 0): bool
    {
        $existing = new Accessory\Listing();
        $existing->setUnpublished(true);
        $existing->setCondition('`key` = ? AND `id` <> ?', [$key, $accessoryId]);
        return count($existing->loadIdList()) > 0;
    }
}
