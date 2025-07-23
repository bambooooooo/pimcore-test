<?php

namespace App\Service;

use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;
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
                    foreach ($this->getPackages($obj) as $lip)
                    {
                        if($lip["Package"]->getDepth()->getValue() > $pricing->getRestrictions()->getMaxPackageLength()->getLimitLength()->getValue()
                            || $lip["Package"]->getWidth()->getValue() > $pricing->getRestrictions()->getMaxPackageLength()->getLimitWidth()->getValue()
                            || $lip["Package"]->getHeight()->getValue() > $pricing->getRestrictions()->getMaxPackageLength()->getLimitHeight()->getValue())
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
                                if($m <= $obj->getPackagesMass()->getValue())
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
                                if($v <= $obj->getPackagesVolume()->getValue())
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

                    if($rule instanceof DataObject\Fieldcollection\Data\PricingAgg)
                    {
                        $prices = [];
                        foreach ($rule->getPricings() as $groupPricing)
                        {
                            $groupPrice = $this->getPricing($obj, $groupPricing);

                            if($groupPrice)
                            {
                                $prices[] = $groupPrice;
                            }
                        }

                        if($prices)
                        {
                            switch ($rule->getOperator())
                            {
                                case "Minimum":
                                {
                                    $price += min($prices);
                                    break;
                                }
                                case "Maximum":
                                {
                                    $price += max($prices);
                                    break;
                                }
                                case "Average":
                                {
                                    $price += array_sum($prices) / count($prices);
                                    break;
                                }
                            }
                        }
                    }

                    if($rule instanceof DataObject\Fieldcollection\Data\MPal)
                    {
                        if(!$obj->getLoadCarriers())
                        {
                            return null;
                        }

                        $carrierPrices = [];

                        foreach ($obj->getLoadCarriers() as $loadCarrier)
                        {
                            if($loadCarrier->isPublished())
                            {
                                $l = $this->ceil($loadCarrier->getLength()->getValue() / 1000);
                                $w = $this->ceil($loadCarrier->getWidth()->getValue() / 1000);
                                $mpal = ceil(100 * $l * $w / 0.96) / 100.0;

                                $carrierPrices[] = $rule->getPrice()->getValue() * $mpal;
                            }
                        }

                        if($carrierPrices)
                        {
                            $price += min($carrierPrices);
                        }
                        else
                        {
                            return null;
                        }
                    }
                }

                if($price <= 0)
                    return null;

                if($pricing->getRate() == 0)
                    throw new \Exception("Pricing rate can not be zero");

                $price = $price / $pricing->getRate();

                if($pricing->getRounding())
                {
                    $price = $this->roundPrettyPrice($price);
                }
                else
                {
                    $price = round($price, 2);
                }

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

    private function roundPrettyPrice(float $price, float $maxDeltaPercent = 5.0): float
    {
        if ($price < 3) {
            $step = 0.10;
            $ending = 0.00;
        } elseif ($price < 10) {
            $step = 0.50;
            $ending = 0.00;
        } elseif ($price < 50) {
            $step = 1.00;
            $ending = 0.00;
        } elseif ($price < 100) {
            $step = 5.00;
            $ending = 0.00;
        } elseif ($price < 500) {
            $step = 10.00;
            $ending = 0.00;
        } elseif ($price < 1000) {
            $step = 25.00;
            $ending = 0.00;
        } else {
            $step = 50.00;
            $ending = 0.00;
        }

        // 1. Round down to nearest "pretty" step
        $core = floor(($price - $ending) / $step) * $step;
        $pretty = $core + $ending;

        // 2. Check if price delta is within allowed threshold
        $deltaPercent = abs($price - $pretty) / $price * 100;

        if ($deltaPercent > $maxDeltaPercent) {
            // Adjust in the opposite direction
            if ($pretty < $price) {
                $pretty += $step;
            } else {
                $pretty -= $step;
            }
        }

        return round($pretty, 2);
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

    private function ceil(float $value, float $resolution = 0.2): float {
        return ceil($value / $resolution) * $resolution;
    }
}
