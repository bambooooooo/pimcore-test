<?php

namespace App\Publishing;

use App\Service\BrokerService;
use App\Service\DeepLService;
use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\Parcel;
use Pimcore\Model\DataObject\QuantityValue\Unit;

class ProductSetPublisher
{
    public function __construct(private readonly BrokerService $broker, private readonly DeepLService $deepLService)
    {

    }

    public function publish(ProductSet $set): void
    {
        $this->assertNamePL($set);
        $this->setItemsValidation($set);

        $this->updateMass($set);
        $this->updatePackageMass($set);
        $this->updatePackageVolumes($set);

        $this->updateParcels($set);

        $this->updateBasePrice($set);
        $this->translateName($set);

        $this->sendToErp($set);
        ApplicationLogger::getInstance()->info("Publishing ProductSet {$set->getId()}");
    }

    function setItemsValidation(ProductSet $set) : void
    {
        assert($set->getSet() and count($set->getSet()) > 0, "ProductSet has no items");

        foreach ($set->getSet() as $lip) {

            $code = $lip->getElement()->getKey();

            assert($lip->getElement()->isPublished(), "ProductSet package [$code] must be published");
            assert($lip->getQuantity() > 0, "ProductSet package [$code] must be greater than 0");
        }
    }

    private function assertNamePL(ProductSet $set)
    {
        assert($set->getName("pl") and strlen($set->getName("pl")) > 3, "ProductSet has to provide name in at least PL");
    }

    private function updateMass(ProductSet $productSet) : void
    {
        $mass = 0;
        foreach ($productSet->getSet() as $li)
        {
            $massItem = $li->getElement()->getMass()->getValue() * (float)$li->getQuantity();

            $mass += $massItem;
        }

        $kg = Unit::getByAbbreviation("kg");
        $productSet->setMass(new QuantityValue($mass, $kg));
    }

    private function updatePackageMass(ProductSet $productSet) : void
    {
        $mass = 0;
        foreach ($productSet->getSet() as $li) {
            foreach($li->getElement()->getPackages() as $lip)
            {
                $massItem = $lip->getElement()->getMass()->getValue() * (float)$lip->getQuantity() * (float)$li->getQuantity();
                $packageId = $lip->getElement()->getId();

                assert($massItem > 0, "Package(Id=$packageId] mass must be greater than 0");

                $mass += $massItem;
            }
        }

        $kg = Unit::getByAbbreviation("kg");
        $productSet->setPackagesMass(new QuantityValue($mass, $kg));
    }

    private function updatePackageVolumes(ProductSet $productSet) : void
    {
        $volume = 0;
        foreach ($productSet->getSet() as $li) {
            foreach($li->getElement()->getPackages() as $lip)
            {
                $v = $lip->getQuantity() * $li->getQuantity()
                    * $lip->getElement()->getWidth()->getValue()
                    * $lip->getElement()->getHeight()->getValue()
                    * $lip->getElement()->getDepth()->getValue();

                $v = ((float)$v) / ((float)1000000000);

                $packageId = $lip->getElement()->getId();

                assert($v > 0, "Package(Id=$packageId] volume must be greater than 0");

                $volume += $v;
            }
        }

        $m3 = Unit::getByAbbreviation("m3");
        $productSet->setPackagesVolume(new QuantityValue($volume, $m3));
    }

    private function updateParcels(ProductSet $set) : void
    {
        $parcels = new Parcel\Listing();
        $parcels->setCondition("`Country` IS NOT NULL AND `published` = 1");

        $productParcels = [];

        foreach ($parcels as $parcel)
        {
            $res = DataObject\Service::useInheritedValues(true, function() use ($parcel, $set){

                $totalMass = $set->getPackagesMass()->getValue();
                $totalVolume = $set->getPackagesVolume()->getValue();

                $packageCount = 0;

                foreach ($set->getSet() as $li) {
                    foreach ($li->getElement()->getPackages() as $lip) {
                        $packageCount += $li->getQuantity() * $lip->getQuantity();
                    }
                }

                $price = 0;
                $item = new ObjectMetadata('Parcel', ['Price'], $parcel);

                if($parcel->getRestrictions())
                {
                    if($parcel->getRestrictions()->getMaxPackageLength())
                    {
                        $limit = $parcel->getRestrictions()->getMaxPackageLength()->getLimit()->getValue();

                        foreach ($set->getSet() as $li)
                        {
                            foreach ($li->getElement()->getPackages() as $lip)
                            {
                                $dim = max([
                                    $lip->getElement()->getWidth()->getValue(),
                                    $lip->getElement()->getHeight()->getValue(),
                                    $lip->getElement()->getDepth()->getValue()
                                ]);

                                if($dim > $limit)
                                {
                                    return null;
                                }
                            }
                        }

                    }

                    if($parcel->getRestrictions()->getMaxPackageWeight())
                    {
                        $limit = $parcel->getRestrictions()->getMaxPackageWeight()->getLimit()->getValue();

                        foreach ($set->getSet() as $li) {
                            foreach ($li->getElement()->getPackages() as $lip) {
                                $dim = $lip->getElement()->getMass()->getValue();

                                if ($dim > $limit) {
                                    return null;
                                }
                            }
                        }
                    }

                    if($parcel->getRestrictions()->getMaxPackageSideLengthSum())
                    {
                        $limit = $parcel->getRestrictions()->getMaxPackageSideLengthSum()->getLimit()->getValue();

                        foreach ($set->getSet() as $li) {
                            foreach ($li->getElement()->getPackages() as $lip) {
                                $dim = $lip->getElement()->getWidth()->getValue() +
                                    $lip->getElement()->getHeight()->getValue() +
                                    $lip->getElement()->getDepth()->getValue();

                                if ($dim > $limit) {
                                    return null;
                                }
                            }
                        }
                    }
                }

                if($parcel->getRules())
                {
                    foreach ($parcel->getRules() as $rule)
                    {
                        if($rule instanceof \Pimcore\Model\DataObject\Fieldcollection\Data\ParcelMassVolume)
                        {
                            $massLimits = [];
                            $volumeLimits = [];

                            $skipCols = 2;
                            $i = 0;

                            foreach ($rule->getPrices()[0] as $headCell)
                            {
                                $i++;
                                if($i <= $skipCols)
                                {
                                    continue;
                                }

                                $massLimits[] = intval(str_replace(",00", "", $headCell));
                            }

                            $skipRows = 2;
                            $j = 0;

                            foreach($rule->getPrices() as $row)
                            {
                                $j++;
                                if($j <= $skipRows)
                                {
                                    continue;
                                }

                                $volumeLimits[] = floatval(str_replace(",", ".", $row[0]));
                            }

                            if($rule->getMode() == "PARCEL")
                            {
                                $x = 0;
                                $y = 0;

                                foreach ($massLimits as $m)
                                {
                                    if($m <= $totalMass)
                                    {
                                        $x++;
                                    }
                                    else
                                    {
                                        break;
                                    }
                                }

                                foreach ($volumeLimits as $v)
                                {
                                    if($v <= $totalVolume)
                                    {
                                        $y++;
                                    }
                                    else
                                    {
                                        break;
                                    }
                                }

                                $y++;
                                $x++;

                                $price += floatval(str_replace(",", ".", $rule->getPrices()[$y][$x]));
                            }
                            elseif($rule->getMode() == "PACKAGE")
                            {
                                foreach ($set->getSet() as $li) {
                                    foreach ($li->getElement()->getPackages() as $lip) {
                                        $x = 0;
                                        $y = 0;

                                        foreach ($massLimits as $m) {
                                            $packageMass = $li->getQuantity() * $lip->getQuantity() * $lip->getElement()->getMass()->getValue();

                                            if ($m <= $packageMass) {
                                                $x++;
                                            } else {
                                                break;
                                            }
                                        }

                                        foreach ($volumeLimits as $v) {
                                            $packageVolume = $li->getQuantity() * $lip->getQuantity()
                                                * $lip->getElement()->getWidth()->getValue()
                                                * $lip->getElement()->getHeight()->getValue()
                                                * $lip->getElement()->getDepth()->getValue();

                                            if ($v <= $packageVolume) {
                                                $y++;
                                            } else {
                                                break;
                                            }
                                        }

                                        $y++;
                                        $x++;

                                        $price += floatval(str_replace(",", ".", $rule->getPrices()[$y][$x])) * $lip->getQuantity();
                                    }
                                }
                            }
                        }

                        if($rule instanceof \Pimcore\Model\DataObject\Fieldcollection\Data\ParcelAddition)
                        {
                            $price += ($rule->getMode() == "PACKAGE") ? $rule->getFee()->getValue() * $packageCount : $rule->getFee()->getValue();
                        }

                        if($rule instanceof \Pimcore\Model\DataObject\Fieldcollection\Data\ParcelFactor)
                        {
                            $price *= $rule->getFactor();
                        }
                    }

                    if($price > 0)
                    {
                        $price = round($price, 2);
                        $item->setPrice($price);
                        return $item;
                    }

                    return null;
                }
            });

            if($res)
            {
                $productParcels[] = $res;
            }
        }

        $set->setParcel($productParcels);
        $set->save();
    }

    function updateBasePrice(ProductSet $productSet) : void
    {
        $price = 0;
        foreach ($productSet->getSet() as $li)
        {
            assert($li->getElement()->getBasePrice()->getValue() > 0, "Product's set item price must be greater than 0");
            assert($li->getQuantity() > 0, "Product's set item quantity must be greater than 0");

            $price += $li->getElement()->getBasePrice()->getValue() * (float)$li->getQuantity();
        }

        $PLN = Unit::getById("PLN");
        $productSet->setBasePrice(new QuantityValue($price, $PLN));
    }

    function translateName(ProductSet $set) : void
    {
        $plName = $set->getName("pl");

        $languages = \Pimcore\Tool::getValidLanguages();

        foreach ($languages as $locale)
        {
            $nameForeign = $set->getName($locale);

            if($nameForeign)
            {
                continue;
            }

            $deeplLocale = ($locale == "en") ? "EN-US" : $locale;

            $tx = $this->deepLService->translate($plName, $deeplLocale, "pl");

            $set->setName($tx, $locale);
            $set->save();
        }
    }

    function sendToErp(ProductSet $productSet) : void
    {
        $name = $productSet->getKey();
        $name = substr($name, 0, min(strlen($name), 50));

        $image = $productSet->getImage()->getThumbnail("200x200");
        $stream = $image->getStream();

        $tempFile = tempnam(sys_get_temp_dir(), 'pim_image_');
        file_put_contents($tempFile, stream_get_contents($stream));

        $imageBase64 = base64_encode(file_get_contents($tempFile));
        unlink($tempFile);

        $packages = [];
        foreach ($productSet->getSet() as $li) {
            foreach($li->getElement()->getPackages() as $lip)
            {
                $packages[] = [
                    'Sku' => $lip->getElement()->getId(),
                    'Count' => (int)$lip->getQuantity() * (int)$li->getQuantity(),
                ];
            }
        }

        $data = [
            "Kind" => "PRODUCT",
            "Sku" => $productSet->getId(),
            "Barcode" => ($productSet->getEan() and strlen($productSet->getEan()) == 13) ? $productSet->getEan() : null,
            "Name" => $name,
            "NameEn" => $productSet->getName("en") ?? "",
            "Description" => $productSet->getName("pl"),
            "Image" => $imageBase64,
            "Mass" => $productSet->getMass()->getValue(),
            "Set" => $packages
        ];

        $this->broker->publishByREST('PRD', 'product', $data);
    }
}
