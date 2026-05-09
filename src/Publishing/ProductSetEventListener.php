<?php

namespace App\Publishing;

use App\Message\BlkIndex;
use App\Message\ErpIndex;
use App\Message\PsMessage;
use App\Message\VizAssignMessage;
use App\Service\OfferService;
use App\Service\PricingService;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\Pricing;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductSetEventListener
{
    public function __construct(private readonly PricingService      $pricingService,
                                private readonly OfferService        $offerService,
                                private readonly MessageBusInterface $bus)
    {

    }

    public function preUpdate(ProductSet $set): void
    {
        DataObject\Service::useInheritedValues(true, function () use ($set) {
            $this->tryUpdatePackageCount($set);
            $this->tryUpdateMass($set);
            $this->tryUpdatePackageMass($set);
            $this->tryUpdatePackagesVolume($set);
            $this->tryUpdateSerieSize($set);
            $this->tryUpdateBasePrice($set);
            $this->tryUpdatePricings($set);
            $this->tryUpdateOffers($set);
            $this->tryUpdateBruttoDimensions($set);
            $this->tryUpdateNettoDimensions($set);
        });
    }

    public function postSave(ProductSet $set): void
    {
        DataObject\Service::useInheritedValues(true, function () use ($set) {
            if ($set->isPublished())
            {
                $this->assertNamePL($set);
                $this->assertProdutsAreAssignedAndPublished($set);

                $this->bus->dispatch(new BlkIndex($set->getId()));
                $this->bus->dispatch(new ErpIndex($set->getId()));
            }

            $this->bus->dispatch(new PsMessage($set->getId()));
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

    private function tryUpdateMass(ProductSet $productSet) : void
    {
        try
        {
            $mass = 0;
            foreach ($productSet->getSet() as $li)
            {
                $mass += $li->getElement()->getMass()->getValue() * (float)$li->getQuantity();
            }

            $kg = Unit::getByAbbreviation("kg");
            $productSet->setMass(new QuantityValue($mass, $kg));
            $productSet->save(["skip" => "mass"]);
        }
        catch (\Throwable $exception)
        {
            $productSet->setMass(null);
            $productSet->save(["skip" => "mass - insufficient data"]);
        }
    }

    private function tryUpdatePackageCount(ProductSet $productSet) : void
    {
        try
        {
            $cnt = 0;
            foreach ($productSet->getSet() as $li) {
                foreach($li->getElement()->getPackages() as $lip)
                {
                    $cnt += $lip->getQuantity() * $li->getQuantity();
                }
            }

            $productSet->setPackageCount($cnt);
            $productSet->save(["skip" => "packages"]);
        }
        catch (\Throwable $exception)
        {
            $productSet->setPackageCount(0);
            $productSet->save(["skip" => "packages - insufficient data"]);
        }
    }

    private function tryUpdatePackageMass(ProductSet $productSet) : void
    {
        try {
            $mass = 0;
            foreach ($productSet->getSet() as $li) {
                foreach ($li->getElement()->getPackages() as $lip) {
                    $massItem = $lip->getElement()->getMass()->getValue() * (float)$lip->getQuantity() * (float)$li->getQuantity();
                    $packageId = $lip->getElement()->getId();

                    assert($massItem > 0, "Package(Id=$packageId] mass must be greater than 0");

                    $mass += $massItem;
                }
            }

            $kg = Unit::getByAbbreviation("kg");
            $productSet->setPackagesMass(new QuantityValue($mass, $kg));

            $productSet->save(["skip" => "packages mass"]);
        }
        catch (\Throwable $exception)
        {
            $productSet->setPackagesMass(null);
            $productSet->save(["skip" => "packages mass - insufficient data"]);
        }
    }

    private function tryUpdatePackagesVolume(ProductSet $productSet) : void
    {
        try
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

            $productSet->save(["skip" => "packages volumes"]);
        }
        catch (\Throwable $exception)
        {
            $productSet->setPackagesVolume(null);
            $productSet->save(["skip" => "packages volume - insufficient data"]);
        }
    }

    private function tryUpdateSerieSize(ProductSet $productSet) : void
    {
        try
        {
            $counts = [];

            foreach ($productSet->getSet() as $li) {
                foreach($li->getElement()->getPackages() as $lip)
                {
                    if($lip->getElement()->getCarriers())
                    {
                        foreach ($lip->getElement()->getCarriers() as $lic)
                        {
                            if($lic->getQuantity())
                            {
                                $counts[] = $lic->getQuantity();
                            }
                            else
                            {
                                $counts[] = 0;
                            }
                        }
                    }
                }
            }

            if(count($counts) > 0 && !array_any($counts, function($c){ return $c == 0; }))
            {
                $lcm = $this->lcmArray($counts);
                $productSet->setSerieSize($lcm);
            }

            $productSet->save(["skip" => "serie size"]);
        }
        catch (\Throwable $exception)
        {
            $productSet->setSerieSize(null);
            $productSet->save(["skip" => "serie size - insufficient data"]);
        }
    }

    private function tryUpdatePricings(ProductSet $set) : void
    {
        try
        {
            $pricingList = new Pricing\Listing();
            $pricingList->setCondition("`published` = 1");

            $productPrices = [];

            foreach ($pricingList as $pricing)
            {
                $price = $this->pricingService->getPricing($set, $pricing);

                if($price)
                {
                    $item = new ObjectMetadata('Pricing', ['Price', 'Currency'], $pricing);
                    $item->setPrice($price);
                    $item->setCurrency($pricing->getCurrency());

                    $productPrices[] = $item;
                }
            }

            $set->setPricing($productPrices);
            $set->save(["skip" => "pricings"]);
        }
        catch (\Throwable $exception)
        {
            $set->setPricing(null);
            $set->save(["skip" => "pricings - insufficient data"]);
        }

    }

    function tryUpdateOffers(ProductSet $set) : void
    {
        try
        {
            $set->setPrice($this->offerService->getObjectPrices($set));
            $set->save(["skip" => "offers"]);
        }
        catch (\Throwable $exception)
        {
            $set->setPrice(null);
            $set->save(["skip" => "offers - insufficient data"]);
        }
    }

    function tryUpdateBasePrice(ProductSet $productSet) : void
    {
        try
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
            $productSet->save(["skip" => "base price"]);
        }
        catch (\Throwable $exception)
        {
            $productSet->setBasePrice(null);
            $productSet->save(["skip" => "base price - insufficient data"]);
        }
    }

    private function tryUpdateBruttoDimensions(ProductSet $set)
    {
        DataObject\Service::useInheritedValues(true, function() use ($set) {
            try {
                $widthBrutto = 0;
                $heightBrutto = 0;
                $depthBrutto = 0;

                $mm = Unit::getByAbbreviation("mm");

                foreach($set->getSet() as $lis)
                {
                    /** @var DataObject\Product $product */
                    $product = $lis->getElement();

                    foreach ($product->getPackages() as $lip) {
                        /** @var DataObject\Package $package */
                        $package = $lip->getElement();

                        $widthBrutto += $package->getWidth()->getValue() * $lip->getQuantity() * $lis->getQuantity();

                        if ($package->getHeight()->getValue() > $heightBrutto) {
                            $heightBrutto = $package->getHeight()->getValue();
                        }

                        if ($package->getDepth()->getValue() > $depthBrutto) {
                            $depthBrutto = $package->getDepth()->getValue();
                        }
                    }
                }

                $changed = false;

                if ($widthBrutto != $set->getWidthBruttoOBI()?->getValue()) {
                    $set->setWidthBruttoOBI(new QuantityValue($widthBrutto, $mm));
                    $changed = true;
                }

                if ($heightBrutto != $set->getHeightBruttoOBI()?->getValue()) {
                    $set->setHeightBruttoOBI(new QuantityValue($heightBrutto, $mm));
                    $changed = true;
                }

                if ($depthBrutto != $set->getLengthBruttoOBI()?->getValue()) {
                    $set->setLengthBruttoOBI(new QuantityValue($depthBrutto, $mm));
                    $changed = true;
                }

                if ($changed) {
                    $set->save(["skip" => "brutto dimensions update (obi)"]);
                }
            } catch (\Throwable $e) {
                //
            }
        });
    }

    function gcd(int $a, int $b): int {
        return $b === 0 ? $a : $this->gcd($b, $a % $b);
    }

    function lcm(int $a, int $b): int {
        return ($a * $b) / $this->gcd($a, $b);
    }

    function lcmArray(array $numbers): int {
        if (empty($numbers)) {
            throw new \InvalidArgumentException("Input array cannot be empty.");
        }

        return array_reduce($numbers, function($carry, $item) {
            return $this->lcm($carry, $item);
        }, 1);
    }

    private function tryUpdateNettoDimensions(ProductSet $set)
    {
        try
        {
            $widthNetto = 0;
            $heightNetto = 0;
            $depthNetto = 0;

            $mm = Unit::getByAbbreviation("mm");

            foreach ($set->getSet() as $li)
            {
                /** @var DataObject\Product $product */
                $product = $li->getElement();

                $widthNetto = max($widthNetto, $product->getWidth()->getValue());
                $heightNetto = $heightNetto + $product->getHeight()->getValue() * $li->getQuantity();
                $depthNetto = max($depthNetto, $product->getDepth()->getValue());
            }

            if($widthNetto * $heightNetto * $depthNetto == 0)
                return;

            $changed = false;

            if($widthNetto != $set->getWidthNettoOBI()?->getValue())
            {
                $set->setWidthNettoOBI(new QuantityValue($widthNetto, $mm));
                $changed = true;
            }

            if($heightNetto != $set->getHeightNettoOBI()?->getValue())
            {
                $set->setHeightNettoOBI(new QuantityValue($heightNetto, $mm));
                $changed = true;
            }

            if($depthNetto != $set->getLengthNettoOBI()?->getValue())
            {
                $set->setLengthNettoOBI(new QuantityValue($depthNetto, $mm));
                $changed = true;
            }

            if($changed)
            {
                $set->save(["skip" => "netto dimensions update (obi)"]);
            }
        }
        catch (\Throwable $exception)
        {
            //
        }
    }

    public function postAdd(ProductSet $obj)
    {
        $this->bus->dispatch(new VizAssignMessage($obj->getId(), "ProductSet"));
    }
}
