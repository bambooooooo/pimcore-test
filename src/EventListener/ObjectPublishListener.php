<?php

namespace App\EventListener;

use App\Publishing\EanPoolPublisher;
use App\Publishing\PackagePublisher;use App\Publishing\PricingPublisher;
use App\Publishing\ProductPublisher;
use App\Publishing\ProductSetPublisher;
use App\Publishing\GroupPublisher;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\EanPool;
use Pimcore\Model\DataObject\Group;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Parcel;
use Pimcore\Model\DataObject\Pricing;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class ObjectPublishListener
{
    public function __construct(
        private readonly ProductPublisher    $productPublisher,
        private readonly ProductSetPublisher $productSetPublisher,
        private readonly PackagePublisher    $packagePublisher,
        private readonly EanPoolPublisher    $eanPoolPublisher,
        private readonly PricingPublisher    $pricingPublisher,
        private readonly GroupPublisher      $groupPublisher,
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
        else if($obj instanceof Pricing and $obj->isPublished())
        {
            $this->pricingPublisher->publish($obj);
        }
        else if($obj instanceof Group and $obj->isPublished())
        {
            $this->groupPublisher->publish($obj);
        }
    }
}
