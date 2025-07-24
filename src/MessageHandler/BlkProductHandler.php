<?php

namespace App\MessageHandler;

use App\Message\BlkIndex;
use App\Service\BaselinkerService;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[AsMessageHandler]
class BlkProductHandler
{
    public function __construct(private readonly BaselinkerService $baselinkerService, private readonly CacheInterface $cache)
    {

    }
    public function __invoke(BlkIndex $message) : void
    {
        $obj = DataObject::getById($message->getObjectId());

        if($obj instanceof ProductSet || $obj instanceof Product) {

            $this->cache->get($obj->getId() . "", function(ItemInterface $item) use ($obj) {
                $item->set($obj->getId() . "");
                $item->expiresAfter(10);

                $this->baselinkerService->export($obj);
            });
        }
        else
        {
            throw new \InvalidArgumentException("Unknown object type");
        }
    }
}
