<?php

namespace App\Publishing;

use App\Service\BrokerService;
use App\Service\DeepLService;
use Pimcore\Model\DataObject\Product;

class ProductPublisher
{
    public function __construct(private readonly BrokerService $broker, private readonly DeepLService $deepLService)
    {

    }

    public function publish(Product $product): void
    {
        \Pimcore\Logger::info("Publishing product {$product->getId()}");

        $this->assertNamePL($product);
        $this->packageValidation($product);

        if($product->getObjectType() == 'ACTUAL')
        {
            $this->updateDefaultBarcode($product);
            $this->translateNames($product);

            $this->sendToErp($product);
        }
    }

    private function assertNamePL(Product $product)
    {
        assert($product->getName("pl") and strlen($product->getName("pl")) > 3, "Product has to provide name in at least PL");
    }

    function packageValidation(Product $product) : void
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

        $languages = \Pimcore\Tool::getValidLanguages();

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
