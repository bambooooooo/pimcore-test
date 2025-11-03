<?php

namespace App\Publishing;

use App\Message\ErpIndex;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Symfony\Component\Messenger\MessageBusInterface;

class PackageEventListener
{
    public function __construct(private readonly MessageBusInterface $bus)
    {

    }

    public function preUpdate(Package $package): void
    {
        DataObject\Service::useInheritedValues(false, function() use ($package) {
            $this->updateDefaultBarcode($package);
        });

        DataObject\Service::useInheritedValues(true, function() use ($package) {
            $this->tryUpdateVolume($package);
        });
    }

    public function postUpdate(Package $package): void
    {
        DataObject\Service::useInheritedValues(true, function() use ($package) {

            if($package->isPublished())
            {
                try
                {
                    $this->assertFillmentHasIntegers($package);
                    $this->assertLayersAreFilled($package);
                }
                catch(\Throwable $e)
                {
                    $package->setPublished(false);
                    $package->save(["skip" => "fillment parsing error"]);
                    throw $e;
                }
            }

            if($package->isPublished() && $package->getObjectType() == 'SKU')
            {
                $this->sendToErp($package);
            }
        });
    }

    public function postAdd(Package $package): void
    {
        $this->updateDefaultBarcode($package);
    }

    private function assertFillmentHasIntegers(Package $package): void
    {
        foreach ($package->getFillment() as $fillmentGroup)
        {
            $gname = $fillmentGroup['Material']->getData();
            $pos = 1;
            foreach ($fillmentGroup['Elements']->getData() as $row)
            {
                assert(intval($row[0]) > 0, "Fillment [" . $gname . ":" . $pos . "] length has to be greater than 0 integer");
                assert(intval($row[1]) > 0, "Fillment [" . $gname . ":" . $pos . "] width has to be greater than 0 integer");
                assert(intval($row[2]) > 0, "Fillment [" . $gname . ":" . $pos . "] quantity has to be greater than 0 integer");

                $pos++;
            }
        }
    }

    function updateDefaultBarcode(Package $package) : void
    {
        if(!$package->getBarcode() && $package->getObjectType() == 'SKU')
        {
            $barcode = "11" . str_pad($package->getId(), 18, "0", STR_PAD_LEFT);
            $package->setBarcode($barcode);
            $package->save(["skip" => "update default barcode"]);
        }
    }

    function tryUpdateVolume(Package $package) : void
    {
        try
        {
            $v = $package->getWidth()->getValue()
                * $package->getHeight()->getValue()
                * $package->getDepth()->getValue();

            $v = ((float)$v) / (1000000000.0);

            $m3 = Unit::getByAbbreviation("m3");

            $package->setVolume(new QuantityValue($v, $m3));
            $package->save(["skip" => "volume update"]);
        }
        catch(\Throwable $e)
        {
            $package->setVolume(null);
            $package->save(["skip" => "volume update - unsufficient data"]);
        }
    }

    function sendToErp(Package $package) : void
    {
        $this->bus->dispatch(new ErpIndex($package->getId()));
    }

    private function assertLayersAreFilled(Package $package)
    {
        foreach($package->getLayers() as $layer)
        {
            assert($layer['Elementy'], "Package layer items must be filled");
        }
    }
}
