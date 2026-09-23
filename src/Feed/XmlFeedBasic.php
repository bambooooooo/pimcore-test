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
                    $data[] = $obj;
                }
            }
        }

        parent::__construct($data, function (Product|ProductSet $item) use ($offer) {

            $priceGetter = 'get' . $offer->getPrice();

            $price = (float)$item->{$priceGetter}()->getValue();
            $price = $price * ((100 - $offer->getDrop() ?? 0.0) / 100);
            $price = round($price, 2);

            if($price == 0.0)
            {
                return "";
            }

            $doc = new DOMDocument('1.0', 'utf-8');
            $doc->formatOutput = true;

            $prod = $doc->createElement('product');
            $prod->setAttribute('id', $item->getId());
            $prod->appendChild($doc->createElement('sku', (string)$item->getId()));
            $prod->appendChild($doc->createElement('name', (string)$item->getName("pl")));
            $prod->appendChild($doc->createElement('instock', $item->getStock()));
            $prod->appendChild($doc->createElement('price', $price));

            return $doc->saveXML($prod);
        });
    }
}
