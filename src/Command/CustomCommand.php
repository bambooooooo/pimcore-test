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
        $this->importCollections("/GRUPY/KOLEKCJE", "/GRUPY");
        $this->importPackages("/PACZKI");
        $this->getProducts("/PRODUKTY", "/PRODUKTY");
        $this->getSuspendedProducts("/PRODUKTY NIEWDROŻONE", "/PRODUKTY");
        $this->getSets("/ZESTAWY", "/ZESTAWY");

        return Command::SUCCESS;
    }

    private function importCollections($GRUPY_PATH, $GRUPY_ASSET_PATH)
    {
        DataObject::setHideUnpublished(false);

        $this->addFolderPath($GRUPY_PATH);
        $GRUPY_ID = DataObject\Folder::getByPath($GRUPY_PATH)->getId();

        $this->addFolderPath($GRUPY_ASSET_PATH, "ASSET");
        $GRUPY_ASSET_ID = Asset\Folder::getByPath($GRUPY_ASSET_PATH)->getId();

        $data = $this->httpClient->request("GET", "http://10.10.100.1/api/v1/product/exportcollections")->toArray();

        $i = 0;
        $total = count($data);

        foreach ($data as $key => $imageURL)
        {
            if(Group::getByPath($GRUPY_PATH . "/" . $key))
            {
                $this->writeComment("Skipping $key.");
                continue;
            }

            $g = new Group();
            $g->setKey($key);
            $g->setParentId($GRUPY_ID);
            $g->setName($key, "pl");

            $imname = explode("/", $imageURL);
            $imname = str_replace("%", "_", $imname[count($imname) - 1]);

            $img = Asset\Image::getByPath($GRUPY_ASSET_PATH . "/" . $imname);
            if($img)
            {
                $g->setImage($img);
            }
            elseif($imageURL)
            {
                $im = new \Pimcore\Model\Asset\Image();
                $im->setFilename($imname);
                $im->setData(file_get_contents($imageURL));
                $im->setParentId($GRUPY_ASSET_ID);
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

    private function importPackages($PACZKI_PATH)
    {
        $this->addFolderPath($PACZKI_PATH);
        $PACZKI_ID = DataObject\Folder::getByPath($PACZKI_PATH)->getId();

        $this->importPackage(146, $PACZKI_ID);
        $this->importPackage(2627, $PACZKI_ID);
        $this->importPackage(5581, $PACZKI_ID);
        $this->importPackage(5582, $PACZKI_ID);
        $this->importPackage(5674, $PACZKI_ID);
        $this->importPackage(5830, $PACZKI_ID);
        $this->importPackage(6243, $PACZKI_ID);
        $this->importPackage(6244, $PACZKI_ID);
        $this->importPackage(6245, $PACZKI_ID);
        $this->importPackage(6246, $PACZKI_ID);
        $this->importPackage(6247, $PACZKI_ID);
        $this->importPackage(6248, $PACZKI_ID);
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

        $parent = DataObject::getById($parentId);
        $path = $parent->getPath() . $parent->getKey() . "/" . $data['key'];

        $curr = DataObject::getByPath($path);
        if($curr)
        {
            $this->writeComment("[~] Skipping " . $data['key'] . " (" . $curr->getId() . ").");
            return $curr->getId();
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

        if($data['MirjanCode'])
        {
            $brick = new DataObject\Objectbrick\Data\IndexMirjan24($package);
            $brick->setCode($data['MirjanCode']);
            $package->getCodes()->setIndexMirjan24($brick);
        }

        $package->save();

        $this->writeInfo('[+] ' . $data['key']);

        return $package->getId();
    }

    private function getProducts($PRODUKTY_PATH, $PRODUKTY_ASSET_PATH)
    {
        $this->addFolderPath($PRODUKTY_PATH);
        $PRODUKTY_FOLDER = DataObject\Folder::getByPath($PRODUKTY_PATH);

        $this->addFolderPath($PRODUKTY_ASSET_PATH, "ASSET");
        $IMAGE_FOLDER = Asset\Folder::getByPath($PRODUKTY_ASSET_PATH);

        $this->importProducts(6249, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6250, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6251, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6252, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6253, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6254, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6255, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6256, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6257, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6258, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6259, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6260, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6261, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6262, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(455, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(456, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(457, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6269, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6267, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6271, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6263, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6264, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6266, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(6265, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
    }

    private function getSuspendedProducts($PRODUKTY_PATH, $PRODUKTY_ASSET_PATH)
    {
        $this->addFolderPath($PRODUKTY_PATH);
        $PRODUKTY_FOLDER = DataObject\Folder::getByPath($PRODUKTY_PATH);

        $this->addFolderPath($PRODUKTY_ASSET_PATH, "ASSET");
        $IMAGE_FOLDER = Asset\Folder::getByPath($PRODUKTY_ASSET_PATH);

        $this->importProducts(1194, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1195, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1196, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1197, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1198, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1199, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1200, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1201, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1202, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1206, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1207, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1208, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1209, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1210, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1211, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1235, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1236, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1237, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1238, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1239, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1240, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1241, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1242, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1243, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1244, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1245, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1246, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1247, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1251, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1252, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1253, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1254, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1255, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1256, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1257, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1258, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1260, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1261, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1262, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1263, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1264, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1265, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1266, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1270, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1271, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1272, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1273, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1274, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1275, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1280, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1281, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1283, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1290, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1291, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1292, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1298, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1299, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1300, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1301, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1302, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1303, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1316, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
        $this->importProducts(1318, $PRODUKTY_FOLDER, $IMAGE_FOLDER);
    }

    private function importProducts($id, $parent, $imageFolder)
    {
        \Pimcore::collectGarbage();
        $res = $this->httpClient->request("GET", "http://10.10.100.1/api/v1/product/export/$id")->toArray();

        $newParent = $this->addProduct($res, $parent, $imageFolder);
        foreach ($res['Children'] as $childId) {
            \Pimcore::collectGarbage();
            $this->importProducts($childId, $newParent, $imageFolder);
        }
    }

    private function addProduct($data, $parent, $imageFolder) : Product
    {
        \Pimcore::collectGarbage();
        DataObject::setHideUnpublished(false);

        $path = $parent->getPath() . $parent->getKey() . "/" . $data['key'];

        $curr = Product::getByPath($path);
        if($curr)
        {
            $this->writeComment("[~] Skipping " . $data['key'] . " (" . $curr->getId() . ").");
            return $curr;
        }

        $kg = Unit::getById('kg');
        $mm = Unit::getById('mm');
        $pln = Unit::getById('PLN');

        $prod = new Product();
        $prod->setKey($data['key']);
        $prod->setParent($parent);

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

        $FILES_FOLDER_PATH = "/PRODUKTY/PLIKI";
        $this->addFolderPath($FILES_FOLDER_PATH, "ASSET");
        $filesFolder = Asset\Folder::getByPath($FILES_FOLDER_PATH);

        // assembly
        if($data['AssemblyGuide'])
        {
            $fileURL = $data['AssemblyGuide'];

            $fileName = explode("/", $fileURL);
            $fileName = str_replace("%", "_", $fileName[count($fileName) - 1]);

            $f = Asset\Document::getByPath($filesFolder->getPath() . $filesFolder->getKey() . "/" . $fileName);
            if($f)
            {
                $prod->setDocuments([$f]);
            }
            else
            {
                $f = new \Pimcore\Model\Asset\Document();
                $f->setFilename($fileName);
                $f->setData(file_get_contents($fileURL));
                $f->setParent($filesFolder);
                $f->save();

                $prod->setDocuments([$f]);
            }
        }

        if($data['Image'])
        {
            $fileURL = $data['Image'];

            $fileName = explode("/", $fileURL);
            $fileName = str_replace("%", "_", $fileName[count($fileName) - 1]);

            $img = Asset\Image::getByPath($imageFolder->getPath() . $imageFolder->getKey() . "/" . $fileName);
            if($img)
            {
                $prod->setImage($img);
            }
            else
            {
                $im = new \Pimcore\Model\Asset\Image();
                $im->setFilename($fileName);
                $im->setData(file_get_contents($fileURL));
                $im->setParent($imageFolder);
                $im->save();

                $prod->setImage($im);
            }
        }

        if($data['Images'])
        {
            $images = $this->addGallery($data['Images'], $imageFolder);
            $prod->setImages(new DataObject\Data\ImageGallery($images));
        }

        if($data['Photos'])
        {
            $images = $this->addGallery($data['Photos'], $imageFolder);
            $prod->setPhotos(new DataObject\Data\ImageGallery($images));
        }

        $images = [];

        if($data['ImagesModel'])
        {
            $images = $this->addGallery($data['ImagesModel'], $imageFolder);
            $prod->setImagesModel(new DataObject\Data\ImageGallery($images));
        }

        $gallery = new DataObject\Data\ImageGallery($images);
        $prod->setImagesModel($gallery);

        if($data['Summary'])
            $prod->setSummary($data['Summary'], "pl");

        $prod->save();

        $this->writeInfo('[+] ' . $data['key']);

        return $prod;
    }

    private function getSets($SETS_PATH, $SETS_ASSETS_PATH)
    {
        $this->addFolderPath($SETS_PATH);
        $FOLDER = DataObject\Folder::getByPath($SETS_PATH);

        $this->addFolderPath($SETS_ASSETS_PATH, "ASSET");
        $IMAGE_FOLDER = Asset\Folder::getByPath($SETS_ASSETS_PATH);

        $this->importSets(6270, $FOLDER, $IMAGE_FOLDER);
    }

    private function importSets($id, $FOLDER, $IMAGE_FOLDER)
    {
        \Pimcore::collectGarbage();
        $res = $this->httpClient->request("GET", "http://10.10.100.1/api/v1/product/export/$id")->toArray();

        $newParent = $this->addSet($res, $FOLDER, $IMAGE_FOLDER);
        foreach ($res['Children'] as $childId) {
            $this->importSets($childId, $newParent, $IMAGE_FOLDER);
        }
    }

    private function addSet($data, $parent, $IMAGE_FOLDER) : ProductSet
    {
        DataObject::setHideUnpublished(false);

        $path = $parent->getPath() . $parent->getKey() . "/" . $data['key'];

        $curr = ProductSet::getByPath($path);
        if($curr)
        {
            $this->writeComment("[~] Skipping " . $data['key'] . " (" . $curr->getId() . ").");
            return $curr;
        }

        $set = new ProductSet();
        $set->setKey($data['key']);
        $set->setParent($parent);

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
            $imname = str_replace("%", "_", $imname[count($imname) - 1]);

            $img = Asset\Image::getByPath($IMAGE_FOLDER . "/" . $imname);
            if($img)
            {
                $set->setImage($img);
            }
            else
            {
                $im = new \Pimcore\Model\Asset\Image();
                $im->setFilename($imname);
                $im->setData(file_get_contents($imurl));
                $im->setParent($IMAGE_FOLDER);
                $im->save();

                $set->setImage($im);
            }
        }

        if($data['Images'])
        {
            $images = $this->addGallery($data['Images'], $IMAGE_FOLDER);
            $gallery = new DataObject\Data\ImageGallery($images);
            $set->setImages($gallery);
        }

        if($data['ImagesModel'])
        {
            $images = $this->addGallery($data['ImagesModel'], $IMAGE_FOLDER);
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

        return $set;
    }

    private function addFolderPath($path, $kind = "DAO")
    {
        $parts = explode("/", $path);
        $currentPath = "";
        $currentParentId = 1;

        for($i = 0; $i < count($parts); $i++)
        {
            $currentPath .= "/" . $parts[$i];

            if($kind == "DAO")
            {
                $c = DataObject::getByPath($currentPath);
                if($c == null)
                {
                    $crumb = new DataObject\Folder();
                    $crumb->setParentId($currentParentId);
                    $crumb->setKey($parts[$i]);
                    $crumb->save();
                    $this->writeInfo("[+] $currentPath");
                    $currentParentId = $crumb->getId();
                }
                else
                {
                    $currentParentId = $c->getId();
                }
            }
            elseif($kind == "ASSET")
            {
                $c = Asset::getByPath($currentPath);
                if ($c == null)
                {
                    $crumb = new Asset\Folder();
                    $crumb->setParentId($currentParentId);
                    $crumb->setKey($parts[$i]);
                    $crumb->save();
                    $this->writeInfo("[+] $currentPath");
                    $currentParentId = $crumb->getId();
                }
                else
                {
                    $currentParentId = $c->getId();
                }
            }
            else
            {
                throw new Exception(("Unknown kind " . $kind));
            }
        }
    }

    private function addGallery($imageURLs, $folder) : array
    {
        $images = [];

        foreach ($imageURLs as $imurl)
        {
            if($imurl)
            {
                $imname = explode("/", $imurl);
                $imname = str_replace("%", "_", $imname[count($imname) - 1]);

                $img = Asset\Image::getByPath($folder . "/" . $imname);
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
                    $im->setParent($folder);
                    $im->save();

                    $hs = new DataObject\Data\Hotspotimage($img);
                    $images[] = $hs;
                }
            }
        }

        return $images;
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
