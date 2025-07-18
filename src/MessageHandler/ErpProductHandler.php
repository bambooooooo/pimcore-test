<?php

namespace App\MessageHandler;

use App\Message\ErpIndex;
use App\Service\SubiektGTService;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ErpProductHandler
{
    public function __construct(private SubiektGTService $subiektGTService, private LoggerInterface $logger)
    {

    }
    public function __invoke(ErpIndex $message): void
    {
        $obj = DataObject::getById($message->getObjectId());

        if($obj instanceof ProductSet) {
            $data = $this->exportProductSetData($obj);
            $endpoint = "products";
        }
        else if ($obj instanceof Product) {
            $data = $this->exportProductData($obj);
            $endpoint = "products";
        }
        else if($obj instanceof Package) {
            $data = $this->exportPackageData($obj);
            $endpoint = "packages";
        }
        else
        {
            throw new \InvalidArgumentException("Unknown object type");
        }

        $res = $this->subiektGTService->request("POST", $endpoint, $data);

        if($res->getStatusCode() == 200)
        {
            $this->logger->info("Ok.");
        }
        else
        {
            $this->logger->info("[Error] " . $res->getContent(false));
            throw new \Exception($res->getContent(false));
        }
    }

    private function exportProductSetData(ProductSet $productSet)
    {
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
                    'Id' => "".$lip->getElement()->getId(),
                    'Quantity' => (int)$lip->getQuantity() * (int)$li->getQuantity(),
                ];
            }
        }

        return [
            "Id" => "".$productSet->getId(),
            "Key" => $productSet->getKey(),
            "NamePl" => $productSet->getName("pl"),
            "NameEn" => $productSet->getName("en") ?? "",
            "Barcode" => ($productSet->getEan() and strlen($productSet->getEan()) == 13) ? $productSet->getEan() : null,
            "Image" => $imageBase64,
            "Mass" => $productSet->getMass()->getValue(),
            "Packages" => $packages,
            "BasePrice" => $productSet->getBasePrice()->getValue(),
            "Prices" => $this->getPrices($productSet)
        ];
    }

    private function exportProductData(Product $product)
    {
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
                'Id' => "".$lip->getElement()->getId(),
                'Quantity' => (int)$lip->getQuantity()
            ];
        }

        return [
            "Id" => "".$product->getId(),
            "Key" => $product->getKey(),
            "NamePl" => $product->getName("pl"),
            "NameEn" => $product->getName("en") ?? "",
            "Ean" => ($product->getEan() and strlen($product->getEan()) == 13) ? "".$product->getEan() : "".$product->getBarcode(),
            "Image" => $imageBase64,
            "Mass" => $product->getMass()->getValue(),
            "Width" => $product->getWidth()->getValue(),
            "Height" => $product->getHeight()->getValue(),
            "Length" => $product->getDepth()->getValue(),
            "Volume" => ((float)($product->getWidth()->getValue() * $product->getHeight()->getValue() * $product->getDepth()->getValue())) / 1000000000,
            "Packages" => $packages,
            "BasePrice" => $product->getBasePrice()->getValue(),
            "Prices" => $this->getPrices($product)
        ];
    }

    private function exportPackageData(Package $package)
    {
        $name = $package->getKey();
        $name = substr($name, 0, min(strlen($name), 50));

        return [
            "Id" => "".$package->getId(),
            "Key" => $package->getKey(),
            "Barcode" => $package->getBarcode() . "",
            "Name" => $name,
            "Description" => $name,
            "Mass" => $package->getMass()->getValue(),
            "Length" => $package->getDepth()->getValue(),
            "Width" => $package->getWidth()->getValue(),
            "Height" => $package->getHeight()->getValue(),
            "Volume" => $package->getVolume()->getValue(),
            "BasePrice" => $package->getBasePrice()->getValue()
        ];
    }

    private function getPrices(Product|ProductSet $p): array
    {
        $prices = [];
        foreach ($p->getPrice() as $priceLevel)
        {
            $prices[] = [
                'Code' => $priceLevel->getElement()->getKey(),
                'Price' => $priceLevel->getPrice()
            ];
        }

        return $prices;
    }
}
