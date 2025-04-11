<?php

namespace App\Publishing;

use App\Service\GS1Service;
use Pimcore\Model\DataObject\EanPool;

class EanPoolPublisher
{
    public function __construct(
        private readonly GS1Service $GS1Service
    ) { }
    public function publish(EanPool $eanPool): void
    {
        if(count($eanPool->getAvailableCodes()) > 1)
        {
            return;
        }

        $this->GS1Service->updateFreeCodes($eanPool);
    }
}
