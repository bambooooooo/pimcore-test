<?php

namespace App\EventListener;

use App\Publishing\EanPoolPublisher;
use App\Publishing\ProductPublisher;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\EanPool;
use Pimcore\Model\DataObject\Product;

class ObjectPublishListener
{
    public function __construct(
        private readonly ProductPublisher $productPublisher,
        private readonly EanPoolPublisher $eanPoolPublisher,
    ) {}
    public function onPublish(ElementEventInterface $event): void
    {
        $obj = $event->getElement();

        if(!$obj->isPublished()) {
            return;
        }

        if($obj instanceof Product)
        {
            $this->productPublisher->publish($obj);
        }
        else if ($obj instanceof EanPool)
        {
            $this->eanPoolPublisher->publish($obj);
        }
    }
}
