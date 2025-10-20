<?php

namespace App\EventListener;

use App\Publishing\BaselinkerCatalogPublisher;
use App\Publishing\BaselinkerPublisher;
use App\Publishing\EanPoolPublisher;
use App\Publishing\OfferPublisher;
use App\Publishing\OrderPublisher;
use App\Publishing\PackageEventListener;
use App\Publishing\PricingPublisher;
use App\Publishing\ProductEventListener;
use App\Publishing\ProductSetEventListener;
use App\Publishing\GroupPublisher;
use App\Publishing\UserPublisher;
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
use Pimcore\Model\DataObject\User;
use Pimcore\Model\DataObject\Service;

class ObjectListener
{
    public function __construct(
        private readonly ProductEventListener       $productEventListener,
        private readonly ProductSetEventListener    $productSetEventListener,
        private readonly PackageEventListener       $packageEventListener,
        private readonly EanPoolPublisher           $eanPoolPublisher,
        private readonly PricingPublisher           $pricingPublisher,
        private readonly GroupPublisher             $groupPublisher,
        private readonly OrderPublisher             $orderPublisher,
        private readonly OfferPublisher             $offerPublisher,
        private readonly BaselinkerPublisher        $baselinkerPublisher,
        private readonly BaselinkerCatalogPublisher $baselinkerCatalogPublisher,
        private readonly UserPublisher              $userPublisher,
    ) {}

    public function preUpdate(ElementEventInterface $event): void
    {
        if($event->hasArgument('skip') || $event->hasArgument('isAutoSave') || $event->hasArgument('saveVersionOnly')) {
            return;
        }

        $obj = $event->getElement();

        if($obj instanceof Product)
        {
            Service::useInheritedValues(true, function() use ($obj) {
                $this->productEventListener->preUpdate($obj);
            });
        }
        else if($obj instanceof ProductSet)
        {
            $this->productSetEventListener->preUpdate($obj);
        }
        else if($obj instanceof Package)
        {
            $this->packageEventListener->preUpdate($obj);
        }
    }
    public function postUpdate(ElementEventInterface $event): void
    {
        if($event->hasArgument('skip') || $event->hasArgument('isAutoSave') || $event->hasArgument('saveVersionOnly')) {
            return;
        }

        $obj = $event->getElement();

        if($obj instanceof Product)
        {
            $this->productEventListener->postUpdate($obj);
        }
        if($obj instanceof ProductSet)
        {
            $this->productSetEventListener->postSave($obj);
        }
        else if ($obj instanceof EanPool and $obj->isPublished())
        {
            $this->eanPoolPublisher->publish($obj);
        }
        else if($obj instanceof Package)
        {
            $this->packageEventListener->postUpdate($obj);
        }
        else if($obj instanceof Pricing and $obj->isPublished())
        {
            $this->pricingPublisher->publish($obj);
        }
        else if($obj instanceof Group)
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
        else if($obj instanceof User and $obj->isPublished())
        {
            $this->userPublisher->publish($obj);
        }
    }

    public function postAdd(ElementEventInterface $event): void
    {
        if($event->hasArgument('skip'))
        {
            return;
        }

        $obj = $event->getElement();

        if($obj instanceof Product)
        {
            $this->productEventListener->postAdd($obj);
        }
    }

    public function preDelete(ElementEventInterface $event): void
    {
        $obj = $event->getElement();

        if($obj instanceof Product)
        {
            $this->productEventListener->preDelete($obj);
        }
    }
}
