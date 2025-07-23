<?php

namespace App\MessageHandler;

use App\Message\BlkIndex;
use App\Service\BaselinkerService;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
class BlkProductHandler
{
    public function __construct(private readonly BaselinkerService $baselinkerService)
    {

    }
    public function __invoke(BlkIndex $message) : void
    {
        $obj = DataObject::getById($message->getObjectId());

        if($obj instanceof ProductSet || $obj instanceof Product) {
            $this->baselinkerService->export($obj);
        }
        else
        {
            throw new \InvalidArgumentException("Unknown object type");
        }
    }
}
