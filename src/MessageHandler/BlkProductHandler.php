<?php

namespace App\MessageHandler;

use App\Message\BlkIndex;
use App\Service\BaselinkerService;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class BlkProductHandler
{
    public function __construct(private readonly BaselinkerService $baselinkerService, private readonly LockFactory $lockFactory)
    {

    }
    public function __invoke(BlkIndex $message) : void
    {
        $k = 'obj_' . $message->getObjectId();
        $lock = $this->lockFactory->createLock($k, 30);

        $lock->acquire(true);

        try
        {
            $obj = DataObject::getById($message->getObjectId());
            if($obj instanceof ProductSet || $obj instanceof Product)
            {
                $this->baselinkerService->export($obj);
            }
            else
            {
                throw new \InvalidArgumentException("Unknown object type");
            }
        }
        finally
        {
            $lock->release();
        }
    }
}
