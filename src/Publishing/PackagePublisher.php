<?php

namespace App\Publishing;

use App\Service\BrokerService;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\QuantityValue\Unit;

class PackagePublisher
{
    public function __construct(private readonly BrokerService $broker)
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
        $name = $package->getKey();
        $name = substr($name, 0, min(strlen($name), 50));

        $data = [
            "Kind" => "PACKAGE",
            "Sku" => $package->getId(),
            "Barcode" => $package->getBarcode(),
            "Name" => $name,
            "Description" => $name,
            "Mass" => $package->getMass()->getValue(),
            "Width" => $package->getWidth()->getValue(),
            "Height" => $package->getHeight()->getValue(),
            "Depth" => $package->getDepth()->getValue(),
            "Volume" => $package->getVolume()->getValue(),
        ];

        $this->broker->publishByREST('PRD', 'product', $data);
    }
}
