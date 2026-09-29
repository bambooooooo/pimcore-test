<?php

namespace App\Feed;

use App\Feed\Writer\JsonFeedWriter;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class JsonFeedBasic extends JsonFeedWriter
{
    public function __construct(Offer $offer, Offer $referenceOffer = null)
    {
        $data = array_merge($offer->getProducts() ?? [], $offer->getSets() ?? []);
        echo 'Found: ' . count($data) . ' items. ' . PHP_EOL;

        parent::__construct($data, function (Product|ProductSet $item) use ($offer) {

            $priceGetter = 'get' . $offer->getPrice();

            $price = (float)$item->{$priceGetter}()->getValue();
            $price = $price * ((100 - $offer->getDrop() ?? 0.0) / 100);
            $price = round($price, 2);

            if($price == 0.0)
            {
                return "";
            }

            $res = [];
            $res['id'] = $item->getId();
            $res['name'] = $item->getName("pl");
            $res['instock'] = $item->getStock() ?? 0;
            $res['price'] = $price;

            return json_encode($res);
        });
    }
}
