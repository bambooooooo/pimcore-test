<?php

namespace App\Publishing;

use App\Service\BrokerService;
use App\Service\DeepLService;
use Pimcore\Logger;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Fieldcollection\Data\ParcelAddition;
use Pimcore\Model\DataObject\Fieldcollection\Data\ParcelFactor;
use Pimcore\Model\DataObject\Fieldcollection\Data\ParcelMassVolume;
use Pimcore\Model\DataObject\Parcel;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Pimcore\Tool;

class ProductPublisher
{
    public function __construct(private readonly BrokerService $broker, private readonly DeepLService $deepLService)
    {

    }

    public function publish(Product $product): void
    {
        Logger::info("Publishing product {$product->getId()}");

        DataObject\Service::useInheritedValues(true, function() use ($product) {

            $this->assertNamePL($product);
            $this->assertPackageQuantityAndPublished($product);

            $this->translateNames($product);
            $this->updatePackagesMassAndVolume($product);

            if($product->getObjectType() == 'ACTUAL')
            {
                $this->updateDefaultBarcode($product);
                $this->updateParcels($product);
                $this->sendToErp($product);
            }
        });
    }

    private function assertNamePL(Product $product) : void
    {
        assert($product->getName("pl") and strlen($product->getName("pl")) > 3, "Product has to provide name in at least PL");
    }

    function assertPackageQuantityAndPublished(Product $product) : void
    {
        if($product->getPackages())
        {
            foreach ($product->getPackages() as $lip) {

                $code = $lip->getElement()->getKey();

                assert($lip->getElement()->isPublished(), "Product package [$code] must be published");
                assert($lip->getQuantity() > 0, "Product package [$code] must be greater than 0");
            }
        }
    }

    function updateDefaultBarcode(Product $product) : void
    {
        if($product->getBarcode() == null || $product->getBarcode() == "")
        {
            $barcode = "11" . str_pad($product->getId(), 18, "0", STR_PAD_LEFT);
            $product->setBarcode($barcode);
            $product->save();
        }
    }

    function translateNames(Product $product) : void
    {
        $plName = $product->getName("pl");

        $languages = Tool::getValidLanguages();

        foreach ($languages as $locale)
        {
            $nameForeign = $product->getName($locale);

            if($nameForeign)
            {
                continue;
            }

            $deeplLocale = ($locale == "en") ? "EN-US" : $locale;

            $tx = $this->deepLService->translate($plName, $deeplLocale, "pl");

            $product->setName($tx, $locale);
            $product->save();
        }
    }

    function updatePackagesMassAndVolume(Product $product) : void
    {
        $mass = 0;
        $volume = 0;

        foreach ($product->getPackages() as $li)
        {
            $mass += $li->getElement()->getMass()->getValue() * $li->getQuantity();

            $v = $li->getQuantity()
                * $li->getElement()->getWidth()->getValue()
                * $li->getElement()->getHeight()->getValue()
                * $li->getElement()->getDepth()->getValue();

            $v = ((float)$v) / (1000000000.0);
            $volume += $v;
        }

        $kg = Unit::getByAbbreviation("kg");
        $m3 = Unit::getByAbbreviation("m3");

        $product->setPackagesMass(new QuantityValue($mass, $kg));
        $product->setPackagesVolume(new QuantityValue($volume, $m3));

        $product->save();
    }

    function updateParcels(Product $product) : void
    {
        $parcels = new Parcel\Listing();
        $parcels->setCondition("`Country` IS NOT NULL AND `published` = 1");

        $productParcels = [];

        foreach ($parcels as $parcel)
        {
            $res = DataObject\Service::useInheritedValues(true, function() use ($parcel, $product){

                $totalMass = $product->getPackagesMass()->getValue();
                $totalVolume = $product->getPackagesVolume()->getValue();

                $price = 0;
                $item = new ObjectMetadata('Parcel', ['Price'], $parcel);

                $packageCount = 0;

                foreach ($product->getPackages() as $lip)
                {
                    $packageCount += $lip->getQuantity();
                }

                if($parcel->getRestrictions())
                {
                    if($parcel->getRestrictions()->getMaxPackageLength())
                    {
                        $limit = $parcel->getRestrictions()->getMaxPackageLength()->getLimit()->getValue();

                        foreach ($product->getPackages() as $lip)
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

                    if($parcel->getRestrictions()->getMaxPackageWeight())
                    {
                        $limit = $parcel->getRestrictions()->getMaxPackageWeight()->getLimit()->getValue();

                        foreach ($product->getPackages() as $lip)
                        {
                            $dim = $lip->getElement()->getMass()->getValue();

                            if($dim > $limit)
                            {
                                return null;
                            }
                        }
                    }

                    if($parcel->getRestrictions()->getMaxPackageSideLengthSum())
                    {
                        $limit = $parcel->getRestrictions()->getMaxPackageSideLengthSum()->getLimit()->getValue();

                        foreach ($product->getPackages() as $lip)
                        {
                            $dim = $lip->getElement()->getWidth()->getValue() +
                                $lip->getElement()->getHeight()->getValue() +
                                $lip->getElement()->getDepth()->getValue();

                            if($dim > $limit)
                            {
                                return null;
                            }
                        }
                    }
                }

                if($product->getLoadCarriers())
                {
                    if($parcel->getRestrictions() and $parcel->getRestrictions()->getLoadCarriers() and $parcel->getRestrictions()->getLoadCarriers()->getLoadCarriers())
                    {
                        $found = false;

                        foreach ($product->getLoadCarriers() as $productCarrier)
                        {
                            foreach ($parcel->getRestrictions()->getLoadCarriers()->getLoadCarriers() as $parcelCarrier)
                            {
                                if($productCarrier->getId() == $parcelCarrier->getId())
                                {
                                    $found = true;
                                }
                            }
                        }

                        if(!$found)
                            return false;
                    }
                    else
                    {
                        return null;
                    }
                }

                if($parcel->getRules())
                {
                    foreach ($parcel->getRules() as $rule)
                    {
                        if($rule instanceof ParcelMassVolume)
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
                                foreach ($product->getPackages() as $lip)
                                {
                                    $x = 0;
                                    $y = 0;

                                    foreach ($massLimits as $m)
                                    {
                                        $packageMass = $lip->getQuantity() * $lip->getElement()->getMass()->getValue();

                                        if($m <= $packageMass)
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
                                        $packageVolume = $lip->getQuantity()
                                            * $lip->getElement()->getWidth()->getValue()
                                            * $lip->getElement()->getHeight()->getValue()
                                            * $lip->getElement()->getDepth()->getValue();

                                        if($v <= $packageVolume)
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

                                    $price += floatval(str_replace(",", ".", $rule->getPrices()[$y][$x])) * $lip->getQuantity();
                                }
                            }
                        }

                        if($rule instanceof ParcelAddition)
                        {
                            $price += ($rule->getMode() == "PACKAGE") ? $rule->getFee()->getValue() * $packageCount : $rule->getFee()->getValue();
                        }

                        if($rule instanceof ParcelFactor)
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
                }

                return null;
            });

            if($res)
            {
                $productParcels[] = $res;
            }
        }

        $product->setParcel($productParcels);
        $product->save();
    }

    function sendToErp(Product $product) : void
    {
        $name = $product->getKey();
        $name = substr($name, 0, min(strlen($name), 50));

        $image = $product->getImage()->getThumbnail("200x200");
        $stream = $image->getStream();

        $tempFile = tempnam(sys_get_temp_dir(), 'pim_image_');
        file_put_contents($tempFile, stream_get_contents($stream));

        $imageBase64 = base64_encode(file_get_contents($tempFile));
        unlink($tempFile);

        $packages = [];
        foreach($product->getPackages() as $lip)
        {
            $packages[] = [
                'Sku' => $lip->getElement()->getId(),
                'Count' => (int)$lip->getQuantity()
            ];
        }

        $data = [
            "Kind" => "PRODUCT",
            "Sku" => $product->getId(),
            "Barcode" => ($product->getEan() and strlen($product->getEan()) == 13) ? $product->getEan() : $product->getBarcode(),
            "Name" => $name,
            "NameEn" => $product->getName("en") ?? "",
            "Description" => $product->getName("pl"),
            "Image" => $imageBase64,
            "Mass" => $product->getMass()->getValue(),
            "Width" => $product->getWidth()->getValue(),
            "Height" => $product->getHeight()->getValue(),
            "Depth" => $product->getDepth()->getValue(),
            "CN" => $product->getCn(),
            "Volume" => ((float)($product->getWidth()->getValue() * $product->getHeight()->getValue() * $product->getDepth()->getValue())) / 1000000000,
            "Set" => $packages
        ];

        $this->broker->publishByREST('PRD', 'product', $data);
    }
}
