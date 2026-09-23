<?php

namespace App\Feed;

use App\Feed\Writer\CsvFeedWriter;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class CsvFeedBasic extends CsvFeedWriter
{
    public function __construct(Offer $offer, Offer $referenceOffer = null)
    {
        $refs = $offer->getDependencies()->getRequires();
        $data = [];

        foreach ($refs as $ref) {
            if($ref['type'] == 'object') {
                $obj = DataObject::getById($ref['id']);
                if($obj instanceof Product || $obj instanceof ProductSet) {
                    $data[] = $obj;
                }
            }
        }

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

            $fields = [
                $item->getId(),
                $item->getStock(),
                $item->getKey(),
                $price,
                $item->getName("pl"),
                $item->getName("en")
            ];

            return join(';', $fields) . PHP_EOL;
        });
    }

    public function begin($stream): void
    {
        $separator = ';';

        $cols = [
            'id',
            'instock',
            'key',
            'price',
            'name',
            'nameEn'
        ];

        fwrite($stream, "\xEF\xBB\xBF"); // BOM for Excel compatibility
        fwrite($stream, join($separator, $cols) . PHP_EOL);
    }
}
