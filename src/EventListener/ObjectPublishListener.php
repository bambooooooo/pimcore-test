<?php

namespace App\EventListener;

use App\Publishing\EanPoolPublisher;
use App\Publishing\PackagePublisher;
use App\Publishing\ProductPublisher;
use App\Publishing\ProductSetPublisher;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\EanPool;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class ObjectPublishListener
{
    public function __construct(
        private readonly ProductPublisher $productPublisher,
        private readonly ProductSetPublisher $productSetPublisher,
        private readonly PackagePublisher $packagePublisher,
        private readonly EanPoolPublisher $eanPoolPublisher
    ) {}
    public function onPublish(ElementEventInterface $event): void
    {
        $obj = $event->getElement();

        if($obj instanceof Product and $obj->isPublished())
        {
            $this->productPublisher->publish($obj);
        }
        if($obj instanceof ProductSet and $obj->isPublished())
        {
            $this->productSetPublisher->publish($obj);
        }
        else if ($obj instanceof EanPool and $obj->isPublished())
        {
            $this->eanPoolPublisher->publish($obj);
        }
        else if($obj instanceof Package and $obj->isPublished())
        {
            $this->packagePublisher->publish($obj);
        }
    }
}
