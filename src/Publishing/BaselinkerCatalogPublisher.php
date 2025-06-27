<?php

namespace App\Publishing;

use App\Service\BaselinkerService;
use Pimcore\Model\DataObject\BaselinkerCatalog;

class BaselinkerCatalogPublisher
{
    public function __construct(private readonly BaselinkerService $baselinker)
    {

    }

    public function updateInventory(BaselinkerCatalog $catalog): void
    {
        $this->baselinker->updateInventory($catalog);
    }
}
