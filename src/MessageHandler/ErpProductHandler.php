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

        return array_merge(
            $this->exportErpObjectCommonData($productSet),
            $this->exportProductOrProductSetData($productSet),
            [
                "Mass" => $productSet->getMass()->getValue(),
                "Packages" => $packages,
            ]
        );
    }

    private function exportProductData(Product $product)
    {
        $packages = [];
        foreach($product->getPackages() as $lip)
        {
            $packages[] = [
                'Id' => "".$lip->getElement()->getId(),
                'Quantity' => (int)$lip->getQuantity()
            ];
        }

        return array_merge(
            $this->exportErpObjectCommonData($product),
            $this->exportProductOrProductSetData($product),
            [
                "Mass" => $product->getMass()->getValue(),
                "Width" => $product->getWidth()->getValue(),
                "Height" => $product->getHeight()->getValue(),
                "Length" => $product->getDepth()->getValue(),
                "Volume" => ((float)($product->getWidth()->getValue() * $product->getHeight()->getValue() * $product->getDepth()->getValue())) / 1000000000,
                "Packages" => $packages,
            ]
        );
    }

    private function exportErpObjectCommonData(Package|Product|ProductSet $obj)
    {
        return [
            "Id" => "".$obj->getId(),
            "Key" => $obj->getKey(),
            "BasePrice" => $obj->getBasePrice()->getValue(),
        ];
    }

    private function exportProductOrProductSetData(Product|ProductSet $obj): array
    {
        $image = $obj->getImage()->getThumbnail("200x200");
        $stream = $image->getStream();

        $tempFile = tempnam(sys_get_temp_dir(), 'pim_image_');
        file_put_contents($tempFile, stream_get_contents($stream));

        $imageBase64 = base64_encode(file_get_contents($tempFile));
        unlink($tempFile);

        return [
            "NamePl" => $obj->getName("pl"),
            "NameEn" => $obj->getName("en") ?? "",
            "Prices" => $this->getPrices($obj),
            "Image" => $imageBase64,
            "Ean" => ($obj->getEan() and strlen($obj->getEan()) == 13) ? $obj->getEan() . "" : null,
        ];
    }

    private function exportPackageData(Package $package)
    {
        $name = $package->getKey();
        $name = substr($name, 0, min(strlen($name), 50));

        return array_merge($this->exportErpObjectCommonData($package), [
            "Description" => $name,
            "Barcode" => $package->getBarcode() . "",
            "Length" => $package->getDepth()->getValue(),
            "Width" => $package->getWidth()->getValue(),
            "Height" => $package->getHeight()->getValue(),
            "Volume" => $package->getVolume()->getValue(),
            "Mass" => $package->getMass()->getValue(),
        ]);
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
