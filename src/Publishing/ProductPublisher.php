<?php

namespace App\Publishing;

use App\Service\BrokerService;
use App\Service\DeepLService;
use App\Service\OfferService;
use App\Service\PricingService;
use InvalidArgumentException;
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
    public function __construct(private readonly BrokerService $broker,
                                private readonly DeepLService $deepLService,
                                private readonly PricingService $pricingService,
                                private readonly OfferService $offerService)
    {

    }

    public function publish(Product $product): void
    {
        Logger::info("Publishing product {$product->getId()}");

        DataObject\Service::useInheritedValues(true, function() use ($product) {

            $this->assertNamePL($product);
            $this->assertPackageQuantityAndPublished($product);
            $this->assertPackageCodeIfProductMirjanCode($product);
            $this->assertPackageCodeIfProductAgataCode($product);
            $this->assertGroupsArePublished($product);

            $this->translateNames($product);
            $this->updatePackagesMassAndVolumeAndSerieSize($product);

            if($product->getObjectType() == 'ACTUAL')
            {
                $this->updateDefaultBarcode($product);
                $this->updatePricing($product);
                $this->updateOffers($product);
                $this->sendToErp($product);
            }
        });
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

    function updatePackagesMassAndVolumeAndSerieSize(Product $product) : void
    {
        $mass = 0;
        $volume = 0;
        $count = 0;

        $counts = [];

        foreach ($product->getPackages() as $li)
        {
            $count += $li->getQuantity();
            $mass += $li->getElement()->getMass()->getValue() * $li->getQuantity();

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

            $v = $li->getQuantity()
                * $li->getElement()->getWidth()->getValue()
                * $li->getElement()->getHeight()->getValue()
                * $li->getElement()->getDepth()->getValue();

            $v = ((float)$v) / (1000000000.0);
            $volume += $v;
        }

        if(count($counts) > 0 && !array_any($counts, function($c){ return $c == 0; }))
        {
            $lcm = $this->lcmArray($counts);
            $product->setSerieSize($lcm);
        }

        $kg = Unit::getByAbbreviation("kg");
        $m3 = Unit::getByAbbreviation("m3");

        $product->setPackagesMass(new QuantityValue($mass, $kg));
        $product->setPackagesVolume(new QuantityValue($volume, $m3));
        $product->setPackageCount($count);

        $product->save();
    }

    function updatePricing(Product $product) : void
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
        $product->save();
    }

    function updateOffers(Product $product) : void
    {
        $offers = $this->offerService->getObjectOffers($product);
        $product->setOffers($offers);
    }

    function getPricing(Product $product, Pricing $pricing)
    {
        $price = $this->pricingService->getPricing($product, $pricing);
        if($price)
        {
            $item = new ObjectMetadata('Pricing', ['Price'], $pricing);
            $item->setPrice($price);
            return $item;
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
