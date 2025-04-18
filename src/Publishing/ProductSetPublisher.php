<?php

namespace App\Publishing;

use App\Service\BrokerService;
use App\Service\DeepLService;
use Pimcore\Bundle\ApplicationLoggerBundle\ApplicationLogger;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\ProductSet;
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

        $this->updateMassFromPackages($set);
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

    private function updateMassFromPackages(ProductSet $productSet) : void
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
        $productSet->setMass(new QuantityValue($mass, $kg));
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
