<?php

namespace App\Feed;

use App\Feed\Writer\XmlFeedWriter;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class XmlFeedBasic extends XmlFeedWriter
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

        parent::__construct($data, function (DataObject $obj) use ($offer)
        {
            return '<product><id>'.$obj->getId().'</id><key>'.$obj->getKey().'</key></product>';
        });
    }
}
