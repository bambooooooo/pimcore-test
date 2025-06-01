<?php

namespace App\Publishing;

use App\Service\BrokerService;
use App\Service\DeepLService;
use App\Service\OfferService;
use App\Service\PricingService;
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
    public function __construct(private readonly BrokerService $broker,
                                private readonly DeepLService $deepLService,
                                private readonly PricingService $pricingService,
                                private readonly OfferService $offerService)
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
            $this->updateOffers($set);

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
        $pricingList->setCondition("`published` = 1");

        $productPrices = [];

        foreach ($pricingList as $pricing)
        {
            $price = $this->pricingService->getPricing($set, $pricing);

            if($price)
            {
                $item = new ObjectMetadata('Pricing', ['Price'], $pricing);
                $item->setPrice($price);
                $productPrices[] = $item;
            }
        }

        $set->setPricing($productPrices);
        $set->save();
    }

    function updateOffers(ProductSet $set) : void
    {
        $offers = $this->offerService->getObjectOffers($set);
        $set->setOffers($offers);
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
