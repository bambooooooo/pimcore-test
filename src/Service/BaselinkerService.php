<?php

namespace App\Service;

use Pimcore\Model\DataObject\Baselinker;
use Pimcore\Model\DataObject\BaselinkerCatalog;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class BaselinkerService
{
    public function __construct(private readonly KernelInterface $kernel, private readonly HttpClientInterface $httpClient)
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

        $data = [
            "name" => $catalog->getName(),
            "description" => $catalog->getDescription(),
            "languages" => $languages,
            "default_language" => $catalog->getLang()
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

        $catalog->setCatalogId($res["inventory_id"]);

        $res = $this->httpClient->request("POST", "https://api.baselinker.com/connector.php", [
            'headers' => [
                'X-BLToken' => $catalog->getBaselinker()->getApiKey()
            ],
            'body' => [
                'method' => 'getInventoryPriceGroups',
                'parameters' => json_encode([])
            ]
        ])->toArray();

        if($res['status'] != "SUCCESS") {
            throw new \Exception($res['error_code'] . ": " . $res['error_message']);
        }

        foreach ($res["price_groups"] as $priceGroup) {
            if($priceGroup["is_default"]) {
                $catalog->setPriceGroupId((int)$priceGroup["price_group_id"]);
            }
        }

        $catalog->save();

        $res = $this->httpClient->request("POST", "https://api.baselinker.com/connector.php", [
            'headers' => [
                'X-BLToken' => $catalog->getBaselinker()->getApiKey()
            ],
            'body' => [
                'method' => 'getInventoryAvailableTextFieldKeys',
                'parameters' => json_encode([
                    'inventory_id' => $catalog->getCatalogId()
                ])
            ]
        ])->toArray();

        if($res['status'] != "SUCCESS") {
            throw new \Exception($res['error_code'] . ": " . $res['error_message']);
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
            $serverAddr = $this->kernel->getEnvironment() == 'dev' ? "http://10.10.1.1/" : $_SERVER['HTTP_ORIGIN'];

            $imurl = $serverAddr . $obj->getImage()->getThumbnail("webp");
            $im = file_get_contents($imurl);
            $b64 = 'data:' . base64_encode($im);
            $images[] = $b64;

            foreach($obj->getImages() as $image)
            {
                $imurl = $serverAddr . $image->getThumbnail("webp");
                $im = file_get_contents($imurl);
                $b64 = 'data:' . base64_encode($im);
                $images[] = $b64;
            }

            if($obj instanceof Product)
            {
                foreach($obj->getPhotos() as $image)
                {
                    $imurl = $serverAddr . $image->getThumbnail("webp");
                    $im = file_get_contents($imurl);
                    $b64 = 'data:' . base64_encode($im);
                    $images[] = $b64;
                }
            }
            elseif($obj instanceof ProductSet)
            {
                foreach($obj->getSet() as $lip)
                {
                    $imurl = $serverAddr . $lip->getElement()->getImage()->getThumbnail("webp");
                    $im = file_get_contents($imurl);
                    $b64 = 'data:' . base64_encode($im);
                    $images[] = $b64;
                }
            }

            foreach($obj->getImagesModel() as $image)
            {
                $imurl = $serverAddr . $image->getThumbnail("webp");
                $im = file_get_contents($imurl);
                $b64 = 'data:' . base64_encode($im);
                $images[] = $b64;
            }
        }

        $data["images"] = $images;

        if($obj instanceof Product) {
            $data["height"] = $obj->getHeight()->getValue() / 10;
            $data["width"] = $obj->getWidth()->getValue() / 10;
            $data["length"] = $obj->getDepth()->getValue() / 10;

            $data["average_cost"] = $obj->getBasePrice()->getValue();
        }

        foreach($obj->getPrice() as $price)
        {
            $c = $price->getElement();

            if($c and $c->isPublished() and $c->getBaselinkerCatalog())
            {
                $data["inventory_id"] = $c->getBaselinkerCatalog()->getCatalogId();

                $relation = null;

                foreach ($obj->getBaselinkerCatalog() as $rel)
                {
                    if($rel->getElement()->getId() == $c->getBaselinkerCatalog()->getId())
                    {
                        $relation = $rel;
                        $data["product_id"] = $rel->getProductId();
                        break;
                    }
                }

                $textFields = [];
                $textFields['name'] = $obj->getName($c->getBaselinkerCatalog()->getLang());

                foreach($c->getBaselinkerCatalog()->getLanguages() as $locale)
                {
                    $textFields["name|" . $locale] = $obj->getName($locale);
                }

                $data["text_fields"] = $textFields;

                $data['prices'] = [
                    $c->getBaselinkerCatalog()->getPriceGroupId() => (float)$price->getPrice()
                ];

                $bundle = [];
                if($obj instanceof ProductSet)
                {
                    foreach($obj->getSet() as $lip)
                    {
                        $found = false;

                        foreach($lip->getElement()->getBaselinkerCatalog() as $rel)
                        {
                            if($rel->getElement()->getId() == $c->getBaselinkerCatalog()->getId())
                            {
                                $bundle[$rel->getProductId()] = $lip->getQuantity();
                                $found = true;
                                break;
                            }
                        }

                        if(!$found)
                        {
                            throw new \Exception("Set item [" . $lip->getElement()->getKey() . "] is not assigned to given catalog [" . $c->getBaselinkerCatalog()->getId() . "]");
                        }
                    }

                    $data["bundle_products"] = $bundle;
                }


                $response = $this->httpClient->request("POST", "https://api.baselinker.com/connector.php", [
                    'headers' => [
                        'X-BLToken' => $c->getBaselinkerCatalog()->getBaselinker()->getApiKey()
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
                    $relation = new ObjectMetadata('BaselinkerCatalog', ['ProductId'], $c->getBaselinkerCatalog());
                    $relation->setProductId($pid);

                    $relations = $obj->getBaselinkerCatalog();
                    $relations[] = $relation;

                    $obj->setBaselinkerCatalog($relations);
                    $obj->save();
                }
            }
        }
    }
}
