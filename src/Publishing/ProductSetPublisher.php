<?php

namespace App\Publishing;

use App\Message\BlkIndex;
use App\Message\ErpIndex;
use App\Service\OfferService;
use App\Service\PricingService;
use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\Pricing;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductSetPublisher
{
    public function __construct(private readonly PricingService    $pricingService,
                                private readonly OfferService      $offerService,
                                private readonly MessageBusInterface $bus)
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
            $this->updatePackageVolumesAndSerieSize($set);

            $this->updateBasePrice($set);
            $this->updatePricings($set);
            $this->updateOffers($set);

            $this->bus->dispatch(new BlkIndex($set->getId()));
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

    private function updatePackageVolumesAndSerieSize(ProductSet $productSet) : void
    {
        $counts = [];
        $volume = 0;
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

        if(count($counts) > 0 && !array_any($counts, function($c){ return $c == 0; }))
        {
            $lcm = $this->lcmArray($counts);
            $productSet->setSerieSize($lcm);
        }

        $m3 = Unit::getByAbbreviation("m3");
        $productSet->setPackagesVolume(new QuantityValue($volume, $m3));
    }

    private function updatePricings(ProductSet $set) : void
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
        $set->save();
    }

    function updateOffers(ProductSet $set) : void
    {
        $set->setPrice($this->offerService->getObjectPrices($set));
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

    function sendToErp(ProductSet $productSet) : void
    {
        $this->bus->dispatch(new ErpIndex($productSet->getId()));
    }
}
