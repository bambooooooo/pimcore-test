<?php

namespace App\Service;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Fieldcollection\Data\Factor;
use Pimcore\Model\DataObject\Fieldcollection\Data\ParcelMassVolume;
use Pimcore\Model\DataObject\Fieldcollection\Data\Surcharge;
use Pimcore\Model\DataObject\Objectbrick\Data\LoadCarriers;
use Pimcore\Model\DataObject\Pricing;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class PricingService
{
    public function getPricing(Product|ProductSet $obj, Pricing $pricing): float|null
    {
        return DataObject\Service::useInheritedValues(true, function() use ($pricing, $obj) {

            $price = 0;

            if ($pricing->getUseBasePrice()) {
                $price = $obj->getBasePrice()->getValue();
            }

            if($pricing->getRestrictions())
            {
                if($pricing->getRestrictions()->getMaxPackageLength())
                {
                    $limit = $pricing->getRestrictions()->getMaxPackageLength()->getLimit()->getValue();

                    foreach ($this->getPackages($obj) as $lip)
                    {
                        $dim = max([
                            $lip["Package"]->getWidth()->getValue(),
                            $lip["Package"]->getHeight()->getValue(),
                            $lip["Package"]->getDepth()->getValue()
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

                    foreach ($this->getPackages($obj) as $lip)
                    {
                        $dim = $lip["Package"]->getMass()->getValue();

                        if($dim > $limit)
                        {
                            return null;
                        }
                    }
                }

                if($pricing->getRestrictions()->getMaxPackageSideLengthSum())
                {
                    $limit = $pricing->getRestrictions()->getMaxPackageSideLengthSum()->getLimit()->getValue();

                    foreach ($this->getPackages($obj) as $packageData)
                    {
                        $dim = $packageData["Package"]->getWidth()->getValue() +
                            $packageData["Package"]->getHeight()->getValue() +
                            $packageData["Package"]->getDepth()->getValue();

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

                    if($obj->getBasePrice()->getValue() < $low || $obj->getBasePrice()->getValue() > $high)
                        return null;
                }

                if($pricing->getRestrictions()->getProductCOO())
                {
                    foreach ($this->getProducts($obj) as $id => $product)
                    {
                        if(!in_array($product["Product"]->getCOO(), $pricing->getRestrictions()->getProductCOO()->getCOO()))
                        {
                            return null;
                        }
                    }
                }

                if($pricing->getRestrictions()->getSelectedGroups())
                {
                    if(!array_intersect($pricing->getRestrictions()->getSelectedGroups()->getGroups(), $obj->getGroups()))
                    {
                        if($obj instanceof Product)
                        {
                            if(!array_intersect([$obj->getGroup()], $pricing->getRestrictions()->getSelectedGroups()->getGroups()))
                            {
                                return null;
                            }
                        }
                        else
                        {
                            return null;
                        }
                    }
                }

                if($pricing->getRestrictions()->getProductDimensions())
                {
                    foreach ($this->getProducts($obj) as $id => $data)
                    {
                        $product = $data["Product"];

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

            if($obj instanceof Product && $obj->getLoadCarriers())
            {
                if($pricing->getRestrictions()?->getLoadCarriers()?->getLoadCarriers())
                {
                    $found = false;

                    foreach ($obj->getLoadCarriers() as $objCarrier)
                    {
                        foreach ($pricing->getRestrictions()->getLoadCarriers()->getLoadCarriers() as $parcelCarrier)
                        {
                            if($objCarrier->getId() == $parcelCarrier->getId())
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
                            foreach ($this->getPackages($obj) as $packageData)
                            {
                                $x = 0;
                                $y = 0;

                                foreach ($massLimits as $m)
                                {
                                    $packageMass = $packageData["Package"]->getMass()->getValue();

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
                                    $packageVolume = $packageData["Package"]->getVolume()->getValue();

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

                                $price += floatval(str_replace(",", ".", $rule->getPrices()[$y][$x])) * $packageData["Quantity"];
                            }
                        }
                    }

                    if($rule instanceof Surcharge)
                    {
                        $price += ($rule->getMode() == "PACKAGE") ? $rule->getFee()->getValue() * $obj->getPackageCount() : $rule->getFee()->getValue();
                    }

                    if($rule instanceof Factor)
                    {
                        $price *= $rule->getFactor();
                    }

                    if($rule instanceof DataObject\Fieldcollection\Data\Pricing)
                    {
                        $otherPrice = $this->getPricing($obj, $rule->getPricing());
                        if(!$otherPrice)
                            return null;

                        $price += $otherPrice;
                    }

                    if($rule instanceof DataObject\Fieldcollection\Data\ParcelVolume)
                    {
                        $price += (float)$obj->getPackagesVolume()->getValue() * (float)$rule->getPrice()->getValue();
                    }
                }

                $price = round($price, 2);

                if($pricing->getRestrictions())
                {
                    if($pricing->getRestrictions()->getMinimalProfit())
                    {
                        $profit = $price - $obj->getBasePrice()->getValue();

                        if($profit < $pricing->getRestrictions()->getMinimalProfit()->getLimit()->getValue())
                        {
                            return null;
                        }
                    }

                    if($pricing->getRestrictions()->getMinimalPercentageProfit())
                    {
                        $profit = $price - $obj->getBasePrice()->getValue();
                        $percentage = ($obj->getBasePrice()->getValue()) ? $profit / $obj->getBasePrice()->getValue() : 0;

                        if($percentage < $pricing->getRestrictions()->getMinimalPercentageProfit()->getLimit() / 100)
                        {
                            return null;
                        }
                    }

                    if($pricing->getRestrictions()->getMinimalMarkup())
                    {
                        $profit = $price - $obj->getBasePrice()->getValue();
                        $markup = ($price) ? 100 * $profit / $price : 0;

                        if($markup < $pricing->getRestrictions()->getMinimalMarkup()->getLimit())
                        {
                            return null;
                        }
                    }
                }

                return $price;
            }
        });
    }

    private function getProducts(Product|ProductSet $obj): array
    {
        $output = [];

        if($obj instanceof Product)
        {
            $output[$obj->getId()] = [
                'Product' => $obj,
                'Quantity' => 1
            ];
        }
        elseif ($obj instanceof ProductSet)
        {
            foreach ($obj->getSet() as $li)
            {
                $output[$li->getElement()->getId()] = [
                    'Product' => $li->getElement(),
                    'Quantity' => $li->getQuantity()
                ];
            }
        }
        else
        {
            throw new \Exception("Unsupported object type");
        }

        return $output;
    }

    private function getPackages(Product|ProductSet $obj): array
    {
        $output = [];
        if($obj instanceof Product)
        {
            foreach ($obj->getPackages() as $lip)
            {
                $output[] = [
                    "Package" => $lip->getElement(),
                    "Quantity" => $lip->getQuantity(),
                ];
            }

            return $output;
        }
        elseif($obj instanceof ProductSet)
        {
            foreach($obj->getSet() as $li)
            {
                foreach ($li->getElement()->getPackages() as $lip)
                {
                    $output[] = [
                        "Package" => $lip->getElement(),
                        "Quantity" => $lip->getQuantity() * $li->getQuantity(),
                    ];
                }
            }

            return $output;
        }

        throw new \Exception("Unsupported object type");
    }
}
