<?php

namespace App\Publishing;

use App\Service\BrokerService;
use App\Service\DeepLService;
use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Fieldcollection\Data\ParcelMassVolume;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\Pricing;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Pimcore\Tool;

class ProductSetPublisher
{
    public function __construct(private readonly BrokerService $broker, private readonly DeepLService $deepLService)
    {

    }

    public function publish(ProductSet $set): void
    {
        DataObject\Service::useInheritedValues(true, function () use ($set) {
            $this->assertNamePL($set);
            $this->assertProdutsAreAssignedAndPublished($set);

            $this->updatePackageCount($set);
            $this->updateMass($set);
            $this->updatePackageMass($set);
            $this->updatePackageVolumes($set);

            $this->updateBasePrice($set);
            $this->updatePricings($set);

            $this->translateName($set);

            $this->sendToErp($set);
            ApplicationLogger::getInstance()->info("Publishing ProductSet {$set->getId()}");
        });
    }

    function assertProdutsAreAssignedAndPublished(ProductSet $set) : void
    {
        assert($set->getSet() and count($set->getSet()) > 0, "ProductSet has no items");

        foreach ($set->getSet() as $lip) {

            $code = $lip->getElement()->getKey();

            assert($lip->getElement()->isPublished(), "ProductSet package [$code] must be published");
            assert($lip->getQuantity() > 0, "ProductSet package [$code] must be greater than 0");
        }
    }

    private function assertNamePL(ProductSet $set) : void
    {
        assert($set->getName("pl") and strlen($set->getName("pl")) > 3, "ProductSet has to provide name in at least PL");
    }

    private function updateMass(ProductSet $productSet) : void
    {
        $mass = 0;
        foreach ($productSet->getSet() as $li)
        {
            $mass += $li->getElement()->getMass()->getValue() * (float)$li->getQuantity();
        }

        $kg = Unit::getByAbbreviation("kg");
        $productSet->setMass(new QuantityValue($mass, $kg));
    }

    private function updatePackageCount(ProductSet $productSet) : void
    {
        $cnt = 0;
        foreach ($productSet->getSet() as $li) {
            foreach($li->getElement()->getPackages() as $lip)
            {
                $cnt += $lip->getQuantity() * $li->getQuantity();
            }
        }

        $productSet->setPackageCount($cnt);
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

                $v = ((float)$v) / (1000000000.0);

                $packageId = $lip->getElement()->getId();

                assert($v > 0, "Package(Id=$packageId] volume must be greater than 0");

                $volume += $v;
            }
        }

        $m3 = Unit::getByAbbreviation("m3");
        $productSet->setPackagesVolume(new QuantityValue($volume, $m3));
    }

    private function updatePricings(ProductSet $set) : void
    {
        $pricingList = new Pricing\Listing();
        $pricingList->setCondition("`Countries` IS NOT NULL AND `published` = 1");

        $productPrices = [];

        foreach ($pricingList as $pricing)
        {
            $res = $this->getPricing($pricing, $set);

            if($res)
            {
                $productPrices[] = $res;
            }
        }

        $set->setPricing($productPrices);
        $set->save();
    }

    private function getPricing($pricing, $set)
    {
        return DataObject\Service::useInheritedValues(true, function() use ($pricing, $set){

            $totalMass = $set->getPackagesMass()->getValue();
            $totalVolume = $set->getPackagesVolume()->getValue();
            $packageCount = $set->getPackageCount();

            $price = 0;
            if($pricing->getUseBasePrice())
            {
                $price = $set->getBasePrice()->getValue();
            }

            $item = new ObjectMetadata('Pricing', ['Price'], $pricing);

            if($pricing->getRestrictions())
            {
                if($pricing->getRestrictions()->getMaxPackageLength())
                {
                    $limit = $pricing->getRestrictions()->getMaxPackageLength()->getLimit()->getValue();

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

                if($pricing->getRestrictions()->getMaxPackageWeight())
                {
                    $limit = $pricing->getRestrictions()->getMaxPackageWeight()->getLimit()->getValue();

                    foreach ($set->getSet() as $li) {
                        foreach ($li->getElement()->getPackages() as $lip) {
                            $dim = $lip->getElement()->getMass()->getValue();

                            if ($dim > $limit) {
                                return null;
                            }
                        }
                    }
                }

                if($pricing->getRestrictions()->getMaxPackageSideLengthSum())
                {
                    $limit = $pricing->getRestrictions()->getMaxPackageSideLengthSum()->getLimit()->getValue();

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

                if($pricing->getRestrictions()->getBasePrice())
                {
                    $low = $pricing->getRestrictions()->getBasePrice()->getRange()->getMinimum();
                    $high = $pricing->getRestrictions()->getBasePrice()->getRange()->getMaximum();

                    if($set->getBasePrice()->getValue() < $low || $set->getBasePrice()->getValue() > $high)
                        return null;
                }

                if($pricing->getRestrictions()->getProductCOO())
                {
                    foreach ($set->getSet() as $li)
                    {
                        if(!in_array($li->getElement()->getCOO(), $pricing->getRestrictions()->getProductCOO()->getCOO()))
                        {
                            return null;
                        }
                    }

                }

                if($pricing->getRestrictions()->getSelectedGroups())
                {
                    if(!in_array($set->getGroup(), $pricing->getRestrictions()->getSelectedGroups()->getGroups()) &&
                        !array_intersect($pricing->getRestrictions()->getSelectedGroups()->getGroups(), $set->getGroups()))
                    {
                        return null;
                    }
                }

                if($pricing->getRestrictions()->getProductDimensions())
                {
                    foreach ($set->getSet() as $li)
                    {
                        $product = $li->getElement();

                        $w = $product->getWidth()->getValue();
                        $h = $product->getHeight()->getValue();
                        $d = $product->getDepth()->getValue();

                        $wRange = $pricing->getRestrictions()->getProductDimensions()->getWidthRange();
                        $hRange = $pricing->getRestrictions()->getProductDimensions()->getHeightRange();
                        $dRange = $pricing->getRestrictions()->getProductDimensions()->getDepthRange();

                        if(($w < $wRange->getMinimum() || $w > $wRange->getMaximum()) ||
                            ($h < $hRange->getMinimum() || $h > $hRange->getMaximum()) ||
                            ($d < $dRange->getMinimum() || $d > $dRange->getMaximum()))
                        {
                            return null;
                        }
                    }
                }
            }

            foreach ($set->getSet() as $li)
            {
                if($li->getElement()->getLoadCarriers())
                {
                    if($pricing->getRestrictions() and $pricing->getRestrictions()->getLoadCarriers() and $pricing->getRestrictions()->getLoadCarriers()->getLoadCarriers())
                    {
                        $found = false;

                        foreach ($li->getElement()->getLoadCarriers() as $productCarrier)
                        {
                            foreach ($pricing->getRestrictions()->getLoadCarriers()->getLoadCarriers() as $parcelCarrier)
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
            }

            if($pricing->getRules())
            {
                foreach ($pricing->getRules() as $rule)
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
                            foreach ($set->getSet() as $li) {
                                foreach ($li->getElement()->getPackages() as $lip) {
                                    $x = 0;
                                    $y = 0;

                                    foreach ($massLimits as $m) {
                                        $packageMass = $lip->getElement()->getMass()->getValue();

                                        if ($m <= $packageMass) {
                                            $x++;
                                        } else {
                                            break;
                                        }
                                    }

                                    foreach ($volumeLimits as $v) {
                                        $packageVolume = $li->getElement()->getVolume();

                                        if ($v <= $packageVolume) {
                                            $y++;
                                        } else {
                                            break;
                                        }
                                    }

                                    $y++;
                                    $x++;

                                    $price += floatval(str_replace(",", ".", $rule->getPrices()[$y][$x]))
                                        * (float) $li->getQuantity()
                                        * (float) $lip->getQuantity();
                                }
                            }
                        }
                    }

                    if($rule instanceof DataObject\Fieldcollection\Data\Surcharge)
                    {
                        $price += ($rule->getMode() == "PACKAGE") ? $rule->getFee()->getValue() * $packageCount : $rule->getFee()->getValue();
                    }

                    if($rule instanceof DataObject\Fieldcollection\Data\Factor)
                    {
                        $price *= $rule->getFactor();
                    }

                    if($rule instanceof DataObject\Fieldcollection\Data\Pricing)
                    {
                        $otherPrice = $this->getPricing($rule->getPricing(), $set);
                        if(!$otherPrice)
                            return null;

                        $price += $otherPrice->getPrice();
                    }

                    if($rule instanceof DataObject\Fieldcollection\Data\ParcelVolume)
                    {
                        $price += (float)$totalVolume * (float)$rule->getPrice()->getValue();
                    }
                }

                $price = round($price, 2);

                if($pricing->getRestrictions())
                {
                    if($pricing->getRestrictions()->getMinimalProfit())
                    {
                        $profit = $price - $set->getBasePrice()->getValue();

                        if($profit < $pricing->getRestrictions()->getMinimalProfit()->getLimit()->getValue())
                        {
                            return null;
                        }
                    }

                    if($pricing->getRestrictions()->getMinimalPercentageProfit())
                    {
                        $profit = $price - $set->getBasePrice()->getValue();
                        $percentage = ($set->getBasePrice()->getValue()) ? $profit / $set->getBasePrice()->getValue() : 0;

                        if($profit < $pricing->getRestrictions()->getMinimalPercentageProfit()->getLimit())
                        {
                            return null;
                        }
                    }

                    if($pricing->getRestrictions()->getMinimalMarkup())
                    {
                        $profit = $price - $set->getBasePrice()->getValue();
                        $markup = ($price) ? 100 * $profit / $price : 0;

                        if($markup < $pricing->getRestrictions()->getMinimalMarkup()->getLimit())
                        {
                            return null;
                        }
                    }
                }

                $item->setPrice($price);
                return $item;
            }

            return null;
        });
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
        $languages = Tool::getValidLanguages();

        foreach ($languages as $locale)
        {
            $nameForeign = $set->getName($locale);

            if($nameForeign)
            {
                continue;
            }

            $deeplLocale = ($locale == "en") ? "EN-US" : $locale;

            $tx = $this->deepLService->translate($set->getName("pl"), $deeplLocale, "pl");

            $set->setName($tx, $locale);
            $set->save();
        }
    }

    function sendToErp(ProductSet $productSet) : void
    {
        $name = substr($productSet->getKey(), 0, min(strlen($productSet->getKey()), 50));

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
