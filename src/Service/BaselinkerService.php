<?php

namespace App\Service;

use Pimcore\Model\DataObject\Baselinker;
use Pimcore\Model\DataObject\BaselinkerCatalog;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class BaselinkerService
{
    public function __construct(private readonly HttpClientInterface $httpClient, private readonly string $appdomain, private readonly CacheInterface $cache)
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

        $fetchMode = "base64"; // base64 -> data:, url -> url:

        if($fetchMode == "base64")
        {
            $images[] = $this->getBaselinkerBase64Image($obj->getImage());

            foreach($obj->getImages() as $image)
            {
                $images[] = $this->getBaselinkerBase64Image($image->getImage());
            }

            if($obj instanceof Product)
            {
                foreach($obj->getPhotos() as $image)
                {
                    $images[] = $this->getBaselinkerBase64Image($image->getImage());
                }
            }
            elseif($obj instanceof ProductSet)
            {
                foreach($obj->getSet() as $lip)
                {
                    $images[] = $this->getBaselinkerBase64Image($lip->getElement()->getImage());
                }
            }

            foreach($obj->getImagesModel() as $image)
            {
                $images[] = $this->getBaselinkerBase64Image($image->getImage());
            }
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
            $catalog = $price->getElement();

            if ($catalog and $catalog->isPublished() and $catalog->getBaselinkerCatalog()) {
                $catalogIds[] = $catalog->getBaselinkerCatalog()->getId();
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
                if($rel->getElement()->getId() == $catalog->getId())
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

            foreach ($obj->getPrice() as $price) {
                if(in_array($price->getElement()->getBaselinkerPriceGroupId(), $priceGroupIds))
                {
                    $data['prices'][$price->getElement()->getBaselinkerPriceGroupId()] = $price->getPrice();
                }
            }

            $bundle = [];
            if($obj instanceof ProductSet)
            {
                foreach($obj->getSet() as $lip)
                {
                    $found = false;

                    foreach($lip->getElement()->getBaselinkerCatalog() as $rel)
                    {
                        if($rel->getElement()->getId() == $catalog->getId())
                        {
                            $bundle[$rel->getProductId()] = $lip->getQuantity();
                            $found = true;
                            break;
                        }
                    }

                    if(!$found)
                    {
                        throw new \Exception("Set item [" . $lip->getElement()->getKey() . "] is not assigned to given catalog [" . $catalog->getBaselinkerCatalog()->getId() . "]");
                    }
                }

                $data["bundle_products"] = $bundle;
            }

            $response = $this->httpClient->request("POST", "https://api.baselinker.com/connector.php", [
                'headers' => [
                    'X-BLToken' => $catalog->getBaselinkerCatalog()->getBaselinker()->getApiKey()
                ],
                'body' => [
                    'method' => 'addInventoryProduct',
                    'parameters' => json_encode($data)
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

                $relations = $obj->getBaselinkerCatalog();
                $relations[] = $relation;

                $obj->setBaselinkerCatalog($relations);
                $changed = true;

                echo '[+] ' . $obj->getKey() . " --> " . $pid . '@' . $data["inventory_id"] . PHP_EOL;
            }
        }

        if($changed) {
            echo '[~] ' . $obj->getKey() . ' updated.' . PHP_EOL;
            $obj->saveVersion();
        }

        echo $obj->getKey() .  ': Done.' . PHP_EOL;
    }

    private function getBaselinkerBase64Image($image): string
    {
        $thumbs = ["webp", "webp_1800", "webp_1600", "webp_1400"];

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

        return "";
    }
}
