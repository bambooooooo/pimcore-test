<?php

namespace App\Feed;

use App\Feed\Writer\XmlFeedWriter;
use DOMDocument;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class XmlFeedBasic extends XmlFeedWriter
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

        parent::__construct($data, function (Product|ProductSet $obj) use ($offer)
        {
            $price = 0.0;

            foreach($obj->getPrice() as $lip)
            {
                if ($lip->getElement()->getId() == $offer->getId())
                {
                    $price = (float)$lip->getPrice();
                }
            }

            $doc = new DOMDocument('1.0', 'utf-8');
            $doc->formatOutput = true;

            $prod = $doc->createElement('product');
            $prod->setAttribute('id', $obj->getId());
            $prod->appendChild($doc->createElement('sku', (string)$obj->getId()));
            $prod->appendChild($doc->createElement('name', (string)$obj->getName("pl")));
            $prod->appendChild($doc->createElement('instock', $obj->getStock()));
            $prod->appendChild($doc->createElement('price', $price));

            return $doc->saveXML($prod);
        });
    }
}
