<?php

namespace App\Publishing;

use App\Service\BrokerService;
use App\Service\DeepLService;
use Pimcore\Logger;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Fieldcollection\Data\Surcharge;
use Pimcore\Model\DataObject\Fieldcollection\Data\Factor;
use Pimcore\Model\DataObject\Fieldcollection\Data\ParcelMassVolume;
use Pimcore\Model\DataObject\Pricing;
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
            $this->assertGroupsArePublished($product);

            $this->translateNames($product);
            $this->updatePackagesMassAndVolume($product);

            if($product->getObjectType() == 'ACTUAL')
            {
                $this->updateDefaultBarcode($product);
                $this->updatePricing($product);
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

    function updatePricing(Product $product) : void
    {
        $pricings = new Pricing\Listing();
        $pricings->setCondition("`Countries` IS NOT NULL AND `published` = 1");

        $productPrices = [];

        foreach ($pricings as $pricing)
        {
            $res = $this->getPricing($product, $pricing);

            if($res)
            {
                $productPrices[] = $res;
            }
        }

        $product->setPricing($productPrices);
        $product->save();
    }

    function getPricing(Product $product, Pricing $pricing)
    {
        return DataObject\Service::useInheritedValues(true, function() use ($pricing, $product){

            $totalMass = $product->getPackagesMass()->getValue();
            $totalVolume = $product->getPackagesVolume()->getValue();

            $price = 0;
            if($pricing->getUseBasePrice())
            {
                $price = $product->getBasePrice()->getValue();
            }

            $item = new ObjectMetadata('Pricing', ['Price'], $pricing);

            $packageCount = 0;

            foreach ($product->getPackages() as $lip)
            {
                $packageCount += $lip->getQuantity();
            }

            if($pricing->getRestrictions())
            {
                if($pricing->getRestrictions()->getMaxPackageLength())
                {
                    $limit = $pricing->getRestrictions()->getMaxPackageLength()->getLimit()->getValue();

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

                if($pricing->getRestrictions()->getMaxPackageWeight())
                {
                    $limit = $pricing->getRestrictions()->getMaxPackageWeight()->getLimit()->getValue();

                    foreach ($product->getPackages() as $lip)
                    {
                        $dim = $lip->getElement()->getMass()->getValue();

                        if($dim > $limit)
                        {
                            return null;
                        }
                    }
                }

                if($pricing->getRestrictions()->getMaxPackageSideLengthSum())
                {
                    $limit = $pricing->getRestrictions()->getMaxPackageSideLengthSum()->getLimit()->getValue();

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

                if($pricing->getRestrictions()->getBasePrice())
                {
                    $low = $pricing->getRestrictions()->getBasePrice()->getRange()->getMinimum();
                    $high = $pricing->getRestrictions()->getBasePrice()->getRange()->getMaximum();

                    if($product->getBasePrice()->getValue() < $low || $product->getBasePrice()->getValue() > $high)
                        return null;
                }

                if($pricing->getRestrictions()->getProductCOO())
                {
                    if(!in_array($product->getCOO(), $pricing->getRestrictions()->getProductCOO()->getCOO()))
                    {
                        return null;
                    }
                }

                if($pricing->getRestrictions()->getSelectedGroups())
                {
                    if(!in_array($product->getGroup(), $pricing->getRestrictions()->getSelectedGroups()->getGroups()) &&
                        !array_intersect($pricing->getRestrictions()->getSelectedGroups()->getGroups(), $product->getGroups()))
                    {
                        return null;
                    }
                }

                if($pricing->getRestrictions()->getProductDimensions())
                {
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

            if($product->getLoadCarriers())
            {
                if($pricing->getRestrictions()?->getLoadCarriers()?->getLoadCarriers())
                {
                    $found = false;

                    foreach ($product->getLoadCarriers() as $productCarrier)
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
                            foreach ($product->getPackages() as $lip)
                            {
                                $x = 0;
                                $y = 0;

                                foreach ($massLimits as $m)
                                {
                                    $packageMass = $lip->getElement()->getMass()->getValue();

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
                                    $packageVolume = $lip->getElement()->getVolume()->getValue();

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

                    if($rule instanceof Surcharge)
                    {
                        $price += ($rule->getMode() == "PACKAGE") ? $rule->getFee()->getValue() * $packageCount : $rule->getFee()->getValue();
                    }

                    if($rule instanceof Factor)
                    {
                        $price *= $rule->getFactor();
                    }

                    if($rule instanceof DataObject\Fieldcollection\Data\Pricing)
                    {
                        $otherPrice = $this->getPricing($product, $rule->getPricing());
                        if(!$otherPrice)
                            return null;

                        $price += $otherPrice->getPrice();
                    }
                }

                $price = round($price, 2);

                if($pricing->getRestrictions())
                {
                    if($pricing->getRestrictions()->getMinimalProfit())
                    {
                        $profit = $price - $product->getBasePrice()->getValue();

                        if($profit < $pricing->getRestrictions()->getMinimalProfit()->getLimit()->getValue())
                        {
                            return null;
                        }
                    }

                    if($pricing->getRestrictions()->getMinimalPercentageProfit())
                    {
                        $profit = $price - $product->getBasePrice()->getValue();
                        $percentage = ($product->getBasePrice()->getValue()) ? $profit / $product->getBasePrice()->getValue() : 0;

                        if($profit < $pricing->getRestrictions()->getMinimalPercentageProfit()->getLimit())
                        {
                            return null;
                        }
                    }

                    if($pricing->getRestrictions()->getMinimalMarkup())
                    {
                        $profit = $price - $product->getBasePrice()->getValue();
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

    private function assertGroupsArePublished(Product $product)
    {
        assert($product->getGroup()?->isPublished(), "Product's main group must be published");

        assert($product->getGroups(), "Product must be assigned to at least one group");

        foreach($product->getGroups() as $group)
        {
            assert($group->isPublished(), "Product's group [" . $group->getKey() . "] must be published");
        }
    }
}
