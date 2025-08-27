<?php

namespace App\Feed;

use App\Feed\Writer\JsonFeedWriter;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class JsonFeedBasic extends JsonFeedWriter
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

            $res = [];
            $res['id'] = $item->getId();
            $res['name'] = $item->getName();

            return json_encode($res);
        });
    }
}
