<?php

namespace App\Publishing;

use App\Service\BaselinkerService;
use Pimcore\Model\DataObject\Baselinker;
use Pimcore\Model\DataObject\BaselinkerCatalog;

class BaselinkerPublisher
{
    public function __construct(private readonly BaselinkerService $baselinker)
    {

    }

    public function publish(Baselinker $baselinker): void
    {
        $this->baselinker->ping($baselinker);
    }
}
