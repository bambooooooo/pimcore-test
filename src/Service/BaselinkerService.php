<?php

namespace App\Service;

use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Baselinker;
use Pimcore\Model\DataObject\BaselinkerCatalog;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BaselinkerService
{
    public function __construct(private readonly HttpClientInterface $httpClient, private readonly string $appdomain)
    {

    }

    public function ping(Baselinker $baselinker): bool
    {
        $response = $this->httpClient->request("POST", "https://api.baselinker.com/connector.php", [
            'headers' => [
                'X-BLToken' => $baselinker->getApiKey()
            ],
            'body' => [
                'method' => 'getInventories',
                'parameters' => json_encode([])
            ]
        ]);

        $data = $response->toArray();

        if($data['status'] != 'SUCCESS')
        {
            throw new \Exception($data['error_code'] . ": " . $data['error_message']);
        }

        return true;
    }

    public function updateInventory(BaselinkerCatalog $catalog): void
    {
        $languages = [];
        foreach ($catalog->getLanguages() as $language) {
            $languages[] = $language;
        }

        $priceGroups = [];
        foreach ($catalog->getOffers() as $offer) {
            $priceGroups[] = $offer->getBaselinkerPriceGroupId();
        }

        $data = [
            "name" => $catalog->getName(),
            "description" => $catalog->getDescription(),
            "languages" => $languages,
            "default_language" => $catalog->getLang(),
            "price_groups" => $priceGroups,
            "default_price_group" => $priceGroups[0]
        ];

        if($catalog->getCatalogId())
        {
            $data['inventory_id'] = $catalog->getCatalogId();
        }

        $response = $this->httpClient->request("POST", "https://api.baselinker.com/connector.php", [
            'headers' => [
                'X-BLToken' => $catalog->getBaselinker()->getApiKey()
            ],
            'body' => [
                'method' => 'addInventory',
                'parameters' => json_encode($data)
            ]
        ]);

        $res = $response->toArray();

        if($res['status'] != "SUCCESS") {
            throw new \Exception($res['error_code'] . ": " . $res['error_message']);
        }

        if(!$catalog->getCatalogId()) {
            $catalog->setCatalogId($res["inventory_id"]);
        }
    }

    public function updatePriceGroup(Offer $offer): void
    {
        $params = [
            'name' => $offer->getKey(),
            'description' => $offer->getKey(),
            'currency' => $offer->getCurrency()
        ];

        if($offer->getBaselinkerPriceGroupId())
        {
            $params['price_group_id'] = $offer->getBaselinkerPriceGroupId();
        }

        $response = $this->httpClient->request("POST", "https://api.baselinker.com/connector.php", [
            'headers' => [
                'X-BLToken' => $offer->getBaselinker()->getApiKey()
            ],
            'body' => [
                'method' => 'addInventoryPriceGroup',
                'parameters' => json_encode($params)
            ]
        ]);

        $data = $response->toArray();

        if($data['status'] != 'SUCCESS')
        {
            throw new \Exception($data['error_code'] . ": " . $data['error_message']);
        }
        else
        {
            if(!$offer->getBaselinkerPriceGroupId())
            {
                $offer->setBaselinkerPriceGroupId($data['price_group_id']);
                $offer->save();
            }
        }
    }

    public function export(Product|ProductSet $obj)
    {
        $data = [
            "is_bundle"=> $obj instanceof ProductSet,
            "ean" => $obj->getEan(),
            "sku" => "" . $obj->getId(),
            "tax_rate" => 23.0,
            "weight" => $obj->getMass()->getValue(),
        ];

        $images = [];

        $MAX_IMAGE_COUNT = 16;
        $fetchMode = "base64"; // base64 -> data:, url -> url:

        if($fetchMode == "base64")
        {
            $firstImages = [];
            $middleImages = [];
            $lastImages = [];

            $firstImages[] = $this->getBaselinkerBase64Image($obj->getImage());

            foreach($obj->getImages() as $image)
            {
                $middleImages[] = $this->getBaselinkerBase64Image($image->getImage());
            }

            if($obj instanceof Product)
            {
                foreach($obj->getPhotos() as $image)
                {
                    $middleImages[] = $this->getBaselinkerBase64Image($image->getImage());
                }
            }
            elseif($obj instanceof ProductSet)
            {
                foreach($obj->getSet() as $lip)
                {
                    /** @var Product $el */
                    $el = $lip->getElement();
                    $lastImages[] = $this->getBaselinkerBase64Image($el->getImage());
                    if($el->getImagesModel())
                    {
                        $lastImages[] = $this->getBaselinkerBase64Image($el->getImagesModel()->current()->getImage());
                    }
                }
            }

            foreach($obj->getImagesModel() as $image)
            {
                $lastImages[] = $this->getBaselinkerBase64Image($image->getImage());
            }

            $middleImagesCount = min([$MAX_IMAGE_COUNT - count($firstImages) - count($lastImages), count($middleImages)]);
            if($middleImagesCount > 0)
            {
                $middleImages = array_slice($middleImages, 0, $middleImagesCount);
            }

            $images = array_merge($firstImages, $middleImages, $lastImages);
        }

        $data["images"] = $images;

        if($obj instanceof Product) {
            $data["height"] = $obj->getHeight()->getValue() / 10;
            $data["width"] = $obj->getWidth()->getValue() / 10;
            $data["length"] = $obj->getDepth()->getValue() / 10;

            $data["average_cost"] = $obj->getBasePrice()->getValue();
        }

        $catalogIds = [];

        foreach($obj->getPrice() as $price) {
            /** @var Offer $offer */
            $offer = $price->getElement();

            if ($offer and $offer->isPublished()) {

                foreach($offer->getDependencies()->getRequiredBy() as $ref)
                {
                    if($ref['type'] != 'object')
                        continue;

                    $refObj = DataObject::getById($ref['id']);

                    if($refObj and $refObj instanceof BaselinkerCatalog) {
                        $catalogIds[] = $refObj->getId();
                    }
                }
            }
        }

        $catalogIds = array_unique($catalogIds);

        $changed = false;

        foreach($catalogIds as $id)
        {
            $catalog = BaselinkerCatalog::getById($id);
            $priceGroupIds = [];
            foreach ($catalog->getOffers() as $offer) {
                $priceGroupIds[] = $offer->getBaselinkerPriceGroupId();
            }

            $data["inventory_id"] = (int)$catalog->getCatalogId();
            unset($data["product_id"]);

            $relation = null;

            foreach ($obj->getBaselinkerCatalog() as $rel)
            {
                /** @var $el BaselinkerCatalog */
                $el = $rel->getElement();

                if($el->getId() == $catalog->getId())
                {
                    $relation = $rel;
                    $data["product_id"] = $rel->getProductId();
                    break;
                }
            }

            $textFields = [];
            $textFields['name'] = $obj->getName($catalog->getLang());
            $textFields["features"] = [];

            if($obj instanceof Product)
            {
                foreach($obj->getParameters()->getGroups() as $group)
                {
                    foreach($group->getKeys() as $key)
                    {
                        if(!is_iterable($key->getValue()))
                        {
                            $textFields["features"][$key->getConfiguration()->getTitle()] = $key->getValue();
                        }
                    }
                }
            }

            foreach($catalog->getLanguages() as $locale)
            {
                $textFields["name|" . $locale] = $obj->getName($locale);
            }

            $data["text_fields"] = $textFields;

            $data['prices'] = [];

            foreach ($obj->getPrice() as $price) {
                /** @var $el Offer */
                $el = $price->getElement();

                if(in_array($el->getBaselinkerPriceGroupId(), $priceGroupIds))
                {
                    $data['prices'][$el->getBaselinkerPriceGroupId()] = $price->getPrice();
                }
            }

            $bundle = [];
            if($obj instanceof ProductSet)
            {
                foreach($obj->getSet() as $lip)
                {
                    $found = false;
                    /** @var Product $product */
                    $product = $lip->getElement();

                    foreach($product->getBaselinkerCatalog() as $rel)
                    {
                        /** @var BaselinkerCatalog $cat */
                        $cat = $rel->getElement();

                        if($cat->getId() == $catalog->getId())
                        {
                            $bundle[$rel->getProductId()] = $lip->getQuantity();
                            $found = true;
                            break;
                        }
                    }

                    if(!$found)
                    {
                        throw new \Exception("Set item [" . $lip->getElement()->getKey() . "] is not assigned to given catalog [" . $catalog->getId() . "]");
                    }
                }

                $data["bundle_products"] = $bundle;
            }

            $payload = json_encode($data);
            $hash = hash("sha256", $payload);

            if($relation)
            {
                $v = $relation->getVersion();
                if($v && $v == $hash)
                {
                    echo 'No changes. Skipping.' . PHP_EOL;
                    return;
                }
            }

            $response = $this->httpClient->request("POST", "https://api.baselinker.com/connector.php", [
                'headers' => [
                    'X-BLToken' => $catalog->getBaselinker()->getApiKey()
                ],
                'body' => [
                    'method' => 'addInventoryProduct',
                    'parameters' => $payload
                ]
            ]);

            $res = $response->toArray();

            if($res['status'] != "SUCCESS") {
                throw new \Exception($res['error_code'] . ": " . $res['error_message']);
            }

            $pid = (int)$res["product_id"];

            if(!$relation)
            {
                $relation = new ObjectMetadata('BaselinkerCatalog', ['ProductId'], $catalog);
                $relation->setProductId($pid);
                $relation->setVersion($hash);

                $relations = $obj->getBaselinkerCatalog();
                $relations[] = $relation;

                $obj->setBaselinkerCatalog($relations);
                $changed = true;

                echo '[+] ' . $obj->getKey() . " --> " . $pid . '@' . $data["inventory_id"] . PHP_EOL;
            }
            else
            {
                $relation->setVersion($hash);
                $changed = true;

                echo '[~] ' . $obj->getKey() . " --> " . $pid . '@' . $data["inventory_id"] . PHP_EOL;
            }
        }

        if($changed) {
            echo '[~] ' . $obj->getKey() . ' updated.' . PHP_EOL;
            $obj->save();
        }

        echo $obj->getKey() .  ': Done.' . PHP_EOL;
    }

    private function getBaselinkerBase64Image(Image $image): string
    {
        $thumbs = ["webp", "webp_1800", "webp_1600", "webp_1400", "webp_1200", "webp_1000"];

        foreach ($thumbs as $thumb)
        {
            $imurl = $this->appdomain . $image->getThumbnail($thumb);
            $im = file_get_contents($imurl);

            $size = round(strlen($im) / (1024 * 1024), 2);

            if($size < 2)
            {
                return 'data:' . base64_encode($im);
            }
        }

        echo "[WARN] No thumbnail for image ". $image->getKey() . " #" . $image->getId() . PHP_EOL;
        return "";
    }
}
