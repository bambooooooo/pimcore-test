<?php

declare(strict_types=1);

namespace App\Command;

use Pimcore\Console\AbstractCommand;
use Pimcore\Model\DataObject;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Group;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\QuantityValue\Unit;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpClient\HttpClient;

#[AsCommand(
    name: 'bamboo:dev',
    description: 'Temp command to import data'
)]
class CustomCommand extends AbstractCommand
{
    public function __construct()
    {
        parent::__construct();

        $this->httpClient = HttpClient::create();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->importCollections();
        $this->importPackages();
        $this->importProducts(6249, 6251);
        $this->importProducts(6250, 6251);
        $this->importProducts(6251, 6251);
        $this->importProducts(6252, 6251);
        $this->importProducts(6253, 6251);
        $this->importProducts(6254, 6251);
        $this->importProducts(6255, 6251);
        $this->importProducts(6256, 6251);
        $this->importProducts(6257, 6251);
        $this->importProducts(6258, 6251);
        $this->importProducts(6259, 6251);
        $this->importProducts(6260, 6251);
        $this->importProducts(6261, 6251);
        $this->importProducts(6262, 6251);

        $this->importProducts(455, 6251);
        $this->importProducts(456, 6251);
        $this->importProducts(457, 6251);

        $this->importProducts(6269, 6251);
        $this->importProducts(6267, 6251);

        $this->importProducts(6271, 6251);

        $this->importProducts(6263, 6251);
        $this->importProducts(6264, 6251);
        $this->importProducts(6266, 6251);
        $this->importProducts(6265, 6251);

        $this->importSets(6270, 8769);

        return Command::SUCCESS;
    }

    private function importCollections()
    {
        DataObject::setHideUnpublished(false);

        $assetGroup = Asset::getByPath("/GRUPY");

        if(!$assetGroup)
        {
            $assetGroup = new Asset\Folder();
            $assetGroup->setParentId(1);
            $assetGroup->setKey("GRUPY");
            $assetGroup->save();

            $this->writeInfo("Asset folder /GRUPY added.");
        }

        $assetGroupCollections = Asset::getByPath("/GRUPY/KOLEKCJE");
        if(!$assetGroupCollections)
        {
            $assetGroupCollections = new Asset\Folder();
            $assetGroupCollections->setParentId($assetGroup->getId());
            $assetGroupCollections->setKey("KOLEKCJE");
            $assetGroupCollections->save();

            $this->writeInfo("Asset folder /GRUPY/KOLEKCJE added.");
        }

        $daoCollections = DataObject::getByPath("/KOLEKCJE");
        if(!$daoCollections)
        {
            $daoCollections = new DataObject\Folder();
            $daoCollections->setParentId(1);
            $daoCollections->setKey("KOLEKCJE");
            $daoCollections->save();

            $this->writeInfo("DataObject folder /KOLEKCJE added.");
        }

        $data = $this->httpClient->request("GET", "http://10.10.100.1/api/v1/product/exportcollections")->toArray();

        $i = 0;
        $total = count($data);

        foreach ($data as $key => $imurl)
        {
            if($this->findDataObjectByKey($key))
            {
                $this->writeComment("Skipping $key.");
                continue;
            }

            $g = new Group();
            $g->setKey($key);
            $g->setParentId($daoCollections->getId());
            $g->setName($key, "pl");

            $imname = explode("/", $imurl);
            $imname = str_replace("%20", "_", $imname[count($imname) - 1]);
            $imname = str_replace("%C5%82", "Ł", $imname);
            $imname = str_replace("%C5%81", "Ł", $imname);

            $img = Asset\Image::getByPath("/GRUPY/KOLEKCJE/" . $imname);
            if($img)
            {
                $g->setImage($img);
            }
            elseif($imurl)
            {
                $im = new \Pimcore\Model\Asset\Image();
                $im->setFilename($imname);
                $im->setData(file_get_contents($imurl));
                $im->setParentId(178);
                $im->save();

                $g->setImage($im);
            }

            $g->save();

            $this->writeInfo("[$i / $total][+] " . $g->getKey());
            $i++;

            if($i % 20 == 0)
                \Pimcore::collectGarbage();
        }
    }

    private function importPackages()
    {
        $root = DataObject::getByPath("/PACZKI");
        if(!$root)
        {
            $root = new DataObject\Folder();
            $root->setParentId(1);
            $root->setKey("PACZKI");
            $root->save();

            $this->writeInfo("DataObject folder /PACZKI added.");
        }

        $this->importPackage(146, 6925);
        $this->importPackage(2627, 6925);
        $this->importPackage(5581, 6925);
        $this->importPackage(5582, 6925);
        $this->importPackage(5674, 6925);
        $this->importPackage(5830, 6925);
        $this->importPackage(6243, 6925);
        $this->importPackage(6244, 6925);
        $this->importPackage(6245, 6925);
        $this->importPackage(6246, 6925);
        $this->importPackage(6247, 6925);
        $this->importPackage(6248, 6925);
    }

    private function importPackage($id, $parentId)
    {
        $res = $this->httpClient->request("GET", "http://10.10.100.1/api/v1/product/export/$id")->toArray();

        $newId = $this->addPackage($res, $parentId);
        foreach ($res['Children'] as $childId) {
            $this->importPackage($childId, $newId);

            \Pimcore::collectGarbage();
        }
    }

    private function addPackage($data, $parentId) : int
    {
        $mm = Unit::getByAbbreviation("mm");
        $kg = Unit::getByAbbreviation("kg");

        $packs = new Package\Listing();
        $packs->setCondition("`key` = '" . $data['key'] . "' AND `parentId` = '" . $parentId . "'");
        $packs->load();

        if($packs)
        {
            foreach ($packs as $pack) {
                $this->writeComment("[~] Skipping " . $data['key'] . " (" . $pack->getId() . ").");
                return $pack->getId();
            }
        }

        $package = new Package();
        $package->setParentId($parentId);
        $package->setKey($data['key']);

        $package->setObjectType($data['ObjectType']);

        if($data['Model'])
            $package->setModel($data['Model']);

        if($data['Mass'])
            $package->setMass(new QuantityValue($data['Mass'], $kg));

        if($data['Width'])
            $package->setWidth(new QuantityValue($data['Width'], $mm));

        if($data['Height'])
            $package->setHeight(new QuantityValue($data['Height'], $mm));

        if($data['Depth'])
            $package->setDepth(new QuantityValue($data['Depth'], $mm));

        $package->save();

        $this->writeInfo('[+] ' . $data['key']);

        return $package->getId();
    }

    private function importProducts($id, $parentId)
    {
        $res = $this->httpClient->request("GET", "http://10.10.100.1/api/v1/product/export/$id")->toArray();

        $newId = $this->addProduct($res, $parentId);
        foreach ($res['Children'] as $childId) {
            $this->importProducts($childId, $newId);

            \Pimcore::collectGarbage();
        }
    }

    private function addProduct($data, $parentId) : int
    {
        DataObject::setHideUnpublished(false);

        $prods = new Product\Listing();
        $prods->setCondition("`key` = '" . $data['key'] . "' AND `parentId` = '" . $parentId . "'");
        $prods->load();

        if($prods)
        {
            foreach ($prods as $prod) {
                $this->writeComment("[~] Skipping " . $data['key'] . " (" . $prod->getId() . ").");
                return $prod->getId();
            }
        }

        $kg = Unit::getById('kg');
        $mm = Unit::getById('mm');
        $pln = Unit::getById('PLN');

        $prod = new Product();
        $prod->setKey($data['key']);
        $prod->setParentId($parentId);

        if($data['ObjectType'])
            $prod->setObjectType($data['ObjectType']);

        if($data['EAN'])
            $prod->setEan($data['EAN']);

        if($data['Name'])
        {
            foreach ($data['Name'] as $loc => $name)
            {
                $prod->setName($name, $loc);
            }
        }

        if($data['Model'])
            $prod->setModel($data['Model']);

        if($data['Mass'])
            $prod->setMass(new QuantityValue($data['Mass'], $kg));

        if($data['Width'])
            $prod->setWidth(new QuantityValue($data['Width'], $mm));

        if($data['Height'])
            $prod->setHeight(new QuantityValue($data['Height'], $mm));

        if($data['Depth'])
            $prod->setDepth(new QuantityValue($data['Depth'], $mm));

        if($data['BasePrice'])
            $prod->setBasePrice(new QuantityValue($data['BasePrice'], $pln));

        if($data['Packages'])
        {
            $refs = [];

            foreach($data['Packages'] as $packageKey => $qty)
            {
                $package = $this->findDataObjectByKey($packageKey);

                if(!$package)
                    throw new \Exception("Package $packageKey not found");

                $lineItem = new DataObject\Data\ObjectMetadata('Packages', ['Quantity'], $package);
                $lineItem->setQuantity($qty);

                $refs[] = $lineItem;
            }

            $prod->setPackages($refs);
        }

        // assembly

        $IMAGE_FOLDER = 57;
        // images

        if($data['Image'])
        {
            $imurl = $data['Image'];

            $imname = explode("/", $imurl);
            $imname = str_replace("%20", "_", $imname[count($imname) - 1]);
            $imname = str_replace("%C5%82", "Ł", $imname);
            $imname = str_replace("%C5%81", "Ł", $imname);

            $img = Asset\Image::getByPath("/PRODUCT/" . $imname);
            if($img)
            {
                $prod->setImage($img);
            }
            else
            {
                $im = new \Pimcore\Model\Asset\Image();
                $im->setFilename($imname);
                $im->setData(file_get_contents($imurl));
                $im->setParentId($IMAGE_FOLDER);
                $im->save();

                $prod->setImage($im);
            }
        }

        if($data['Images'])
        {
            $images = [];

            foreach ($data['Images'] as $imurl)
            {
                if($imurl)
                {
                    $imname = explode("/", $imurl);
                    $imname = str_replace("%20", "_", $imname[count($imname) - 1]);
                    $imname = str_replace("%C5%82", "Ł", $imname);
                    $imname = str_replace("%C5%81", "Ł", $imname);

                    $img = Asset\Image::getByPath("/PRODUCT/" . $imname);
                    if($img)
                    {
                        $hs = new DataObject\Data\Hotspotimage($img);
                        $images[] = $hs;
                    }
                    else
                    {
                        $im = new \Pimcore\Model\Asset\Image();
                        $im->setFilename($imname);
                        $im->setData(file_get_contents($imurl));
                        $im->setParentId($IMAGE_FOLDER);
                        $im->save();

                        $hs = new DataObject\Data\Hotspotimage($img);
                        $images[] = $hs;
                    }
                }
            }

            $gallery = new DataObject\Data\ImageGallery($images);
            $prod->setImages($gallery);
        }

        if($data['Photos'])
        {
            $images = [];

            foreach ($data['Photos'] as $imurl)
            {
                if($imurl)
                {
                    $imname = explode("/", $imurl);
                    $imname = str_replace("%20", "_", $imname[count($imname) - 1]);
                    $imname = str_replace("%C5%82", "Ł", $imname);
                    $imname = str_replace("%C5%81", "Ł", $imname);

                    $img = Asset\Image::getByPath("/PRODUCT/" . $imname);
                    if($img)
                    {
                        $hs = new DataObject\Data\Hotspotimage($img);
                        $images[] = $hs;
                    }
                    else
                    {
                        $im = new \Pimcore\Model\Asset\Image();
                        $im->setFilename($imname);
                        $im->setData(file_get_contents($imurl));
                        $im->setParentId($IMAGE_FOLDER);
                        $im->save();

                        $hs = new DataObject\Data\Hotspotimage($img);
                        $images[] = $hs;
                    }
                }
            }

            $gallery = new DataObject\Data\ImageGallery($images);
            $prod->setPhotos($gallery);
        }

        $images = [];

        foreach ($data['ImagesModel'] as $imurl)
        {
            if($imurl)
            {
                $imname = explode("/", $imurl);
                $imname = str_replace("%20", "_", $imname[count($imname) - 1]);
                $imname = str_replace("%C5%82", "Ł", $imname);
                $imname = str_replace("%C5%81", "Ł", $imname);

                $img = Asset\Image::getByPath("/PRODUCT/" . $imname);
                if($img)
                {
                    $hs = new DataObject\Data\Hotspotimage($img);
                    $images[] = $hs;
                }
                else
                {
                    $im = new \Pimcore\Model\Asset\Image();
                    $im->setFilename($imname);
                    $im->setData(file_get_contents($imurl));
                    $im->setParentId($IMAGE_FOLDER);
                    $im->save();

                    $hs = new DataObject\Data\Hotspotimage($img);
                    $images[] = $hs;
                }
            }
        }

        $gallery = new DataObject\Data\ImageGallery($images);
        $prod->setImagesModel($gallery);

        if($data['Summary'])
            $prod->setSummary($data['Summary'], "pl");

        $prod->save();

        $this->writeInfo('[+] ' . $data['key']);

        return $prod->getId();
    }

    private function importSets($id, $parentId)
    {
        $res = $this->httpClient->request("GET", "http://10.10.100.1/api/v1/product/export/$id")->toArray();

        $newId = $this->addSet($res, $parentId);
        foreach ($res['Children'] as $childId) {
            $this->importSets($childId, $newId);

            \Pimcore::collectGarbage();
        }
    }

    private function addSet($data, $parentId) : int
    {
        DataObject::setHideUnpublished(false);

        $sets = new ProductSet\Listing();
        $sets->setCondition("`key` = '" . $data['key'] . "' AND `parentId` = '" . $parentId . "'");
        $sets->load();

        if($sets)
        {
            foreach ($sets as $set) {
                $this->writeComment("[~] Skipping " . $data['key'] . " (" . $set->getId() . ").");
                return $set->getId();
            }
        }

        $set = new ProductSet();
        $set->setKey($data['key']);
        $set->setParentId($parentId);

        if($data['EAN'])
            $set->setEan($data['EAN']);

        if($data['Name'])
        {
            foreach ($data['Name'] as $loc => $name)
            {
                $set->setName($name, $loc);
            }
        }

        if($data['Image'])
        {
            $imurl = $data['Image'];

            $imname = explode("/", $imurl);
            $imname = str_replace("%20", "_", $imname[count($imname) - 1]);
            $imname = str_replace("%C5%82", "Ł", $imname);
            $imname = str_replace("%C5%81", "Ł", $imname);

            $img = Asset\Image::getByPath("/ZESTAWY/" . $imname);
            if($img)
            {
                $set->setImage($img);
            }
            else
            {
                $im = new \Pimcore\Model\Asset\Image();
                $im->setFilename($imname);
                $im->setData(file_get_contents($imurl));
                $im->setParentId(1798);
                $im->save();

                $set->setImage($im);
            }
        }

        if($data['Images'])
        {
            $images = [];

            foreach ($data['Images'] as $imurl)
            {
                if($imurl)
                {
                    $imname = explode("/", $imurl);
                    $imname = str_replace("%20", "_", $imname[count($imname) - 1]);
                    $imname = str_replace("%C5%82", "Ł", $imname);
                    $imname = str_replace("%C5%81", "Ł", $imname);

                    $img = Asset\Image::getByPath("/ZESTAWY/" . $imname);
                    if($img)
                    {
                        $hs = new DataObject\Data\Hotspotimage($img);
                        $images[] = $hs;
                    }
                    else
                    {
                        $im = new \Pimcore\Model\Asset\Image();
                        $im->setFilename($imname);
                        $im->setData(file_get_contents($imurl));
                        $im->setParentId(1798);
                        $im->save();

                        $hs = new DataObject\Data\Hotspotimage($img);
                        $images[] = $hs;
                    }
                }
            }

            $gallery = new DataObject\Data\ImageGallery($images);
            $set->setImages($gallery);
        }

        if($data['ImagesModel'])
        {
            $images = [];

            foreach ($data['ImagesModel'] as $imurl)
            {
                if($imurl)
                {
                    $imname = explode("/", $imurl);
                    $imname = str_replace("%20", "_", $imname[count($imname) - 1]);
                    $imname = str_replace("%C5%82", "Ł", $imname);
                    $imname = str_replace("%C5%81", "Ł", $imname);


                    $img = Asset\Image::getByPath("/ZESTAWY/" . $imname);
                    if($img)
                    {
                        $hs = new DataObject\Data\Hotspotimage($img);
                        $images[] = $hs;
                    }
                    else
                    {
                        $im = new \Pimcore\Model\Asset\Image();
                        $im->setFilename($imname);
                        $im->setData(file_get_contents($imurl));
                        $im->setParentId(1798);
                        $im->save();

                        $hs = new DataObject\Data\Hotspotimage($img);
                        $images[] = $hs;
                    }
                }
            }

            $gallery = new DataObject\Data\ImageGallery($images);
            $set->setImagesModel($gallery);
        }

        if($data['Set'])
        {
            $refs = [];

            foreach($data['Set'] as $productKey => $qty)
            {
                $product = $this->findDataObjectByKey($productKey);

                if(!$product)
                    throw new \Exception("Product $productKey not found");

                $lineItem = new DataObject\Data\ObjectMetadata('Set', ['Quantity'], $product);
                $lineItem->setQuantity($qty);

                $refs[] = $lineItem;
            }

            $set->setSet($refs);
        }

        $set->save();

        $this->writeInfo('[+] ' . $data['key']);

        return $set->getId();
    }

    /**
     * Recursively searches for a DataObject object by key.
     *
     * @param string $key The key of the DataObject.
     * @return DataObject|null The found DataObject object or null.
     */
    function findDataObjectByKey(string $key): ?DataObject
    {
        $list = new \Pimcore\Model\DataObject\Listing();
        $list->setCondition("`key` = ?", [$key]);
        $list->setLimit(1);

        $products = $list->load();
        return $products[0] ?? null;
    }
}
