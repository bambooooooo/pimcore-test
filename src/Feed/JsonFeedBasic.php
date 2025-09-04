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
        $refs = $offer->getDependencies()->getRequiredBy();
        $data = [];

        foreach ($refs as $ref) {
            if($ref['type'] == 'object') {
                $obj = DataObject::getById($ref['id']);
                if($obj instanceof Product || $obj instanceof ProductSet) {

                    $price = 0.0;

                    foreach($obj->getPrice() as $lip)
                    {
                        if ($lip->getElement()->getId() == $offer->getId())
                        {
                            $price = (float)$lip->getPrice();
                        }
                    }

                    if($price > 0)
                    {
                        $data[] = $obj;
                    }
                }
            }
        }

        parent::__construct($data, function (Product|ProductSet $item) use ($offer) {

            $price = 0.0;

            foreach($item->getPrice() as $lip)
            {
                if ($lip->getElement()->getId() == $offer->getId())
                {
                    $price = (float)$lip->getPrice();
                }
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
