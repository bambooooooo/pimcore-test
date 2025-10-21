<?php

namespace App\Publishing;

use App\Message\BlkIndex;
use App\Message\ErpIndex;
use App\Message\PsMessage;
use App\Service\OfferService;
use App\Service\PricingService;
use InvalidArgumentException;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Pricing;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Symfony\Component\Messenger\MessageBusInterface;

class ProductEventListener
{
    public function __construct(private readonly PricingService $pricingService,
                                private readonly MessageBusInterface $bus,
                                private readonly OfferService $offerService)
    {

    }

    public function preUpdate(Product $product): void
    {
        $this->tryUpdateTotalMassAndVolume($product);
        $this->tryUpdateSerieSize($product);
        $this->tryUpdatePricing($product);
        $this->tryUpdateOffers($product);

        if($product->isPublished())
        {
            try
            {
                DataObject\Service::useInheritedValues(true, function() use ($product) {
                    $this->assertNamePL($product);
                    $this->assertPackageQuantityAndPublished($product);
                    $this->assertPackageCodeIfProductMirjanCode($product);
                    $this->assertPackageCodeIfProductAgataCode($product);
                    $this->assertPrestashopGroupIsPublished($product);
                    $this->assertGroupsArePublished($product);
                });
            }
            catch(\Throwable $e)
            {
                $product->setPublished(false);
                $product->save(["skip" => "validation errors"]);
                throw $e;
            }
        }
    }

    public function postUpdate(Product $product): void
    {
        DataObject\Service::useInheritedValues(true, function() use ($product) {

            if($product->getObjectType() == 'ACTUAL')
            {
                $this->bus->dispatch(new PsMessage($product->getId()));

                if($product->isPublished())
                {
                    $this->bus->dispatch(new ErpIndex($product->getId()));
                    $this->bus->dispatch(new BlkIndex($product->getId()));
                }
            }
        });
    }

    public function postAdd(Product $product)
    {
        $product->setPs_megstyl_pl(false);
        $product->save(['skip' => 'fix megstyl.pl checkbox']);
    }

    public function preDelete(Product $product): void
    {
        if($product->getPs_megstyl_pl_id())
        {
            $this->bus->dispatch(new PsMessage($product->getPs_megstyl_pl_id(), "delete"));
        }
    }

    private function assertPrestashopGroupIsPublished(Product $product): void
    {
        if(!$product->getPs_megstyl_pl())
            return;

        assert($product->getPs_megstyl_pl_parent(), "Product has no prestashop group");
        assert($product->getPs_megstyl_pl_parent()->getPs_megstyl_pl(), 'Product prestashop group has no prestashop integration selected');
        assert($product->getPs_megstyl_pl_parent()->getPs_megstyl_pl_id(), "Product prestashop group has no prestashop id");
    }

    private function assertPackageCodeIfProductAgataCode(Product $product): void
    {
        if($product->getCodes()?->getIndexAgata())
        {
            foreach($product->getPackages() as $lip)
            {
                $code = $lip->getElement()->getKey();
                assert($lip->getElement()->getCodes()?->getIndexAgata()?->getBarcode(), "Package [$code] has to get Agata barcode");
                assert($lip->getElement()->getCodes()?->getIndexAgata()?->getCode(), "Package [$code] has to get Agata index");
            }
        }
    }

    private function assertPackageCodeIfProductMirjanCode(Product $product): void
    {
        if($product->getCodes()?->getIndexMirjan24()?->getCode())
        {
            foreach($product->getPackages() as $lip)
            {
                $code = $lip->getElement()->getKey();
                assert($lip->getElement()->getCodes()->getIndexMirjan24()?->getCode(), "Package [$code] has to get Mirjan 24 index, because product already got");
            }
        }
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

            assert($product->getPackagesMass()->getValue() > 0, "Product packages total mass must be greater than 0");
            assert($product->getPackageCount() > 0, "Product packages total count must be greater than 0");
            assert($product->getPackagesVolume()->getValue() > 0, "Product packages volume must be greater than 0");
        }
    }

    function updateProductSets(Product $product): void
    {
        $refs = $product->getDependencies()->getRequiredBy();
        foreach($refs as $ref)
        {
            if($ref['type'] == 'object')
            {
                $obj = DataObject::getById($ref['id']);
                if($obj instanceof DataObject\ProductSet)
                {
                    $basePrice = 0.0;
                    foreach($obj->getSet() as $lip)
                    {
                        /** @var ProductSet $prod */
                        $prod = $lip->getElement();
                        $basePrice += $prod->getBasePrice()->getValue() * $lip->getQuantity();
                    }

                    $obj->getBasePrice()->setValue($basePrice);
                    $obj->save();
                }
            }
        }
    }

    function tryUpdateSerieSize(Product $product): void
    {
        try
        {
            if(!$product->getPackages())
                return;

            $counts = [];

            foreach ($product->getPackages() as $li)
            {
                if($li->getElement()->getCarriers())
                {
                    foreach ($li->getElement()->getCarriers() as $lic)
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

            if(count($counts) > 0 && !array_any($counts, function($c){ return $c == 0; }))
            {
                $lcm = $this->lcmArray($counts);
                $product->setSerieSize($lcm);
            }
        }
        catch(\Throwable $e)
        {

        }
    }

    function tryUpdateTotalMassAndVolume(Product $product) : void
    {
        try
        {
            $mass = 0;
            $volume = 0;
            $count = 0;

            foreach ($product->getPackages() as $li)
            {
                $count += $li->getQuantity();
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
            $product->setPackageCount($count);

            $product->save(["skip" => "package data update"]);
        }
        catch(\Throwable $e)
        {
            $product->setPackagesMass(null);
            $product->setPackagesVolume(null);
            $product->setPackageCount(null);
            $product->setSerieSize(null);

            $product->save(["skip" => "unsufficient packages data"]);
        }
    }

    function tryUpdatePricing(Product $product) : void
    {
        try
        {
            $pricings = new Pricing\Listing();
            $pricings->setCondition("`published` = 1");

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
            $product->save(["skip" => "pricing data update"]);
        }
        catch(\Throwable $e)
        {
            $product->setPricing(null);
            $product->save(["skip" => "pricing data - insufficient data"]);
        }
    }

    function tryUpdateOffers(Product $product) : void
    {
        try
        {
            $product->setPrice($this->offerService->getObjectPrices($product));
            $product->save(["skip" => "offers data update"]);
        }
        catch(\Throwable $e)
        {
            $product->setPrice(null);
            $product->save(["skip" => "offers data - insufficient data"]);
        }
    }

    function getPricing(Product $product, Pricing $pricing)
    {
        $price = $this->pricingService->getPricing($product, $pricing);
        if($price)
        {
            $item = new ObjectMetadata('Pricing', ['Price', 'Currency'], $pricing);
            $item->setPrice($price);
            $item->setCurrency($pricing->getCurrency());

            return $item;
        }
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

    function gcd(int $a, int $b): int {
        return $b === 0 ? $a : $this->gcd($b, $a % $b);
    }

    function lcm(int $a, int $b): int {
        return ($a * $b) / $this->gcd($a, $b);
    }

    function lcmArray(array $numbers): int {
        if (empty($numbers)) {
            throw new InvalidArgumentException("Input array cannot be empty.");
        }

        return array_reduce($numbers, function($carry, $item) {
            return $this->lcm($carry, $item);
        }, 1);
    }
}
