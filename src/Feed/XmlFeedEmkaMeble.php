<?php

namespace App\Feed;

use App\Feed\Writer\XmlFeedWriter;
use DOMDocument;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class XmlFeedEmkaMeble extends XmlFeedWriter
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

        parent::__construct($data, function(Product|ProductSet $item) use ($offer, $referenceOffer) {

            $doc = new DOMDocument('1.0', 'utf-8');
            $doc->formatOutput = true;
            $prod = $doc->createElement('o');
            $prod->setAttribute('id', $item->getId());
            $prod->setAttribute('basket', 1);
            $prod->setAttribute('stock', $item->getStock());

            return $doc->saveXML($prod);
        });
    }

    public function begin($stream): void
    {
        fwrite($stream, "<?xml version=\"1.0\" encoding=\"UTF-8\"?><offers xmlns:xsi=\"http://www.w3.org/2001/XMLSchema-instance\" version=\"1\">");
    }

    public function end($stream): void
    {
        fwrite($stream, "</offers>");
    }
}
