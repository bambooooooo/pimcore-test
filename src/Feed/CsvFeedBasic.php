<?php

namespace App\Feed;

use App\Feed\Writer\CsvFeedWriter;
use App\Feed\Writer\FeedWriter;
use Closure;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class CsvFeedBasic extends CsvFeedWriter
{
    public function __construct(Offer $offer)
    {
        $refs = $offer->getDependencies()->getRequiredBy();
        $data = [];

        foreach ($refs as $ref) {
            if($ref['type'] == 'object') {
                $obj = DataObject::getById($ref['id']);
                if($obj instanceof Product || $obj instanceof ProductSet) {
                    $data[] = $obj;
                }
            }
        }

        parent::__construct($data, function (Product|ProductSet $item) use ($offer) {

            $fields = [
                $item->getId(),
                $item->getName()
            ];

            return join(';', $fields) . PHP_EOL;
        });
    }

    public function begin($stream): void
    {
        $separator = ';';

        $cols = [
            'id',
            'name'
        ];

        fwrite($stream, "\xEF\xBB\xBF"); // BOM for Excel compatibility
        fwrite($stream, join($separator, $cols) . PHP_EOL);
    }
}
