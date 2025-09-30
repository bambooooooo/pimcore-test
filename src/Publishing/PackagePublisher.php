<?php

namespace App\Publishing;

use App\Message\ErpIndex;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Symfony\Component\Messenger\MessageBusInterface;

class PackagePublisher
{
    public function __construct(private readonly MessageBusInterface $bus)
    {

    }

    public function publish(Package $package): void
    {
        DataObject\Service::useInheritedValues(true, function() use ($package) {

            $this->updateVolume($package);

            if($package->getObjectType() == 'SKU')
            {
                $this->updateDefaultBarcode($package);
                $this->sendToErp($package);
            }
        });
    }

    function updateDefaultBarcode(Package $product) : void
    {
        if($product->getBarcode() == null || $product->getBarcode() == "")
        {
            $barcode = "11" . str_pad($product->getId(), 18, "0", STR_PAD_LEFT);
            $product->setBarcode($barcode);
            $product->save();
        }
    }

    function updateVolume(Package $package) : void
    {
        $v = $package->getWidth()->getValue()
            * $package->getHeight()->getValue()
            * $package->getDepth()->getValue();

        $v = ((float)$v) / (1000000000.0);

        $m3 = Unit::getByAbbreviation("m3");

        $package->setVolume(new QuantityValue($v, $m3));
        $package->save();
    }

    function sendToErp(Package $package) : void
    {
        $this->bus->dispatch(new ErpIndex($package->getId()));
    }
}
