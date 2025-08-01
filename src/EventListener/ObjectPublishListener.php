<?php

namespace App\EventListener;

use App\Publishing\BaselinkerCatalogPublisher;
use App\Publishing\BaselinkerPublisher;
use App\Publishing\EanPoolPublisher;
use App\Publishing\OfferPublisher;
use App\Publishing\OrderPublisher;
use App\Publishing\PackagePublisher;
use App\Publishing\PricingPublisher;
use App\Publishing\ProductPublisher;
use App\Publishing\ProductSetPublisher;
use App\Publishing\GroupPublisher;
use Pimcore\Event\Model\ElementEventInterface;
use Pimcore\Model\DataObject\Baselinker;
use Pimcore\Model\DataObject\BaselinkerCatalog;
use Pimcore\Model\DataObject\EanPool;
use Pimcore\Model\DataObject\Group;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Order;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Pricing;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class ObjectPublishListener
{
    public function __construct(
        private readonly ProductPublisher           $productPublisher,
        private readonly ProductSetPublisher        $productSetPublisher,
        private readonly PackagePublisher           $packagePublisher,
        private readonly EanPoolPublisher           $eanPoolPublisher,
        private readonly PricingPublisher           $pricingPublisher,
        private readonly GroupPublisher             $groupPublisher,
        private readonly OrderPublisher             $orderPublisher,
        private readonly OfferPublisher             $offerPublisher,
        private readonly BaselinkerPublisher        $baselinkerPublisher,
        private readonly BaselinkerCatalogPublisher $baselinkerCatalogPublisher,
    ) {}
    public function onPublish(ElementEventInterface $event): void
    {
        $obj = $event->getElement();

        if($event->hasArgument('saveVersionOnly'))
        {
            return;
        }

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
        else if($obj instanceof Order and $obj->isPublished())
        {
            $this->orderPublisher->publish($obj);
        }
        else if($obj instanceof Offer and $obj->isPublished())
        {
            $this->offerPublisher->publish($obj);
        }
        else if($obj instanceof Baselinker and $obj->isPublished())
        {
            $this->baselinkerPublisher->publish($obj);
        }
        else if($obj instanceof BaselinkerCatalog and $obj->isPublished())
        {
            $this->baselinkerCatalogPublisher->updateInventory($obj);
        }
    }
}
