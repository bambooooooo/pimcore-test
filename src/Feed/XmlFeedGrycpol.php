<?php

namespace App\Feed;

use App\Feed\Writer\XmlFeedWriter;
use DOMDocument;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class XmlFeedGrycpol extends XmlFeedWriter
{
    public function __construct(Offer $offer, Offer $referenceOffer)
    {
        if(!$referenceOffer)
        {
            throw new \Exception("Reference offer is null");
        }

        $refs = $offer->getDependencies()->getRequiredBy();
        $data = [];

        foreach ($refs as $ref) {
            if($ref['type'] == 'object') {
                $obj = DataObject::getById($ref['id']);
                if($obj instanceof Product || $obj instanceof ProductSet) {

                    $price = 0.0;
                    $endPrice = 0.0;

                    foreach($obj->getPrice() as $lip)
                    {
                        if($lip->getElement()->getId() == $offer->getId())
                            $price = (float)$lip->getPrice();

                        if($lip->getElement()->getId() == $referenceOffer->getId())
                            $endPrice = (float)$lip->getPrice();
                    }

                    if($price * $endPrice > 0.0)
                    {
                        $data[] = $obj;
                    }
                }
            }
        }

        parent::__construct($data, function (Product|ProductSet $item) use ($offer, $referenceOffer) {

            $price = 0.0;
            $endPrice = 0.0;

            foreach($item->getPrice() as $lip)
            {
                if($lip->getElement()->getId() == $offer->getId())
                    $price = (float)$lip->getPrice();

                if($lip->getElement()->getId() == $referenceOffer->getId())
                    $endPrice = (float)$lip->getPrice();
            }

            $doc = new DomDocument('1.0', 'utf-8');
            $doc->formatOutput = true;

            $prod = $doc->createElement('Towar');
            $prod->appendChild($doc->createElement('Symbol', $item->getId()));
            $prod->appendChild($doc->createElement('KodKreskowy', $item->getEan() ?? ""));
            $prod->appendChild($doc->createElement('Nazwa', $item->getName("pl") ?? ""));
            $prod->appendChild($doc->createElement('Jednostka', "szt."));
            $prod->appendChild($doc->createElement('Gwarancja', "24 mies."));
            $prod->appendChild($doc->createElement('CenaNetto', $price));
            $prod->appendChild($doc->createElement('StawkaVAT', 23));
            $prod->appendChild($doc->createElement('CenaBrutto', $price * 1.23, ));
            $prod->appendChild($doc->createElement('Stan', $item->getStock()));
            $prod->appendChild($doc->createElement('TerminRealizacji', ($item->getStock() > 0 ? 'Do 3 dni' : 'Do 28 dni')));
            $prod->appendChild($doc->createElement('Kategoria', $item->getRealFullPath()));
            $prod->appendChild($doc->createElement('Model', $item->getModel()));
            $prod->appendChild($doc->createElement('Typ', $item?->getGroup()?->getName("pl") ?? ""));
            $prod->appendChild($doc->createElement('Wysokosc', $item->getHeight()));
            $prod->appendChild($doc->createElement('Szerokosc', $item->getWidth()));
            $prod->appendChild($doc->createElement('Glebokosc', $item->getDepth()));
            $prod->appendChild($doc->createElement('Waga', $item->getMass()));
            $prod->appendChild($doc->createElement('Zestaw', ($item instanceof ProductSet ? 'Tak' : 'Nie')));
            $prod->appendChild($doc->createElement('Nowosc', ''));
            $prod->appendChild($doc->createElement('Promocja', ''));
            $prod->appendChild($doc->createElement('Wyprzedaz', $item->getStatus() == 'Sale' ? 'Tak' : 'Nie'));
            $prod->appendChild($doc->createElement('ZdjecieURL', $item->getImage()->getFrontendPath()));

            $i = 1;
            /** @var DataObject\Data\Hotspotimage $image */
            foreach ($item->getImagesModel() as $image)
            {
                $prod->appendChild($doc->createElement('ZdjecieModel' . $i . 'URL', $image->getImage()->getFrontendPath()));
                $i++;
            }

            $i = 1;
            /** @var DataObject\Data\Hotspotimage $image */
            foreach ($item->getImages() as $image)
            {
                $prod->appendChild($doc->createElement('Zdjecie' . $i .'URL', $image->getImage()->getFrontendPath()));
                $i++;
            }

            if($item->getParameters() && $item->getParameters()->getGroups())
            {
                foreach ($item->getParameters()->getGroups()[0]->getKeys() as $k)
                {
                    $cfg = $k->getConfiguration();

                    $value = $k->getValue();

                    if(is_array($value))
                    {
                        $value = implode(',', $value);
                    }

                    if(!$value)
                        continue;

                    $label = "";

                    if ($cfg->getType() === 'select') {
                        $definition = json_decode($cfg->getDefinition(), true); // contains options array

                        foreach ($definition['options'] as $option) {
                            if ($option['value'] == $k->getValue()) {
                                $label = $option['key']; // this is the "display name"
                                break;
                            }
                        }
                    }

                    $parNameChunks = preg_split('/[ \-]+/', $cfg->getTitle());
                    $parName = implode(array_map('ucfirst', $parNameChunks));
                    $prod->appendChild($doc->createElement($parName, $value ?? "" ));
                }
            }

            return $doc->saveXML($prod);
        });
    }

    public function begin($stream): void
    {
        fwrite($stream, "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Towary>");
    }

    public function end($stream): void
    {
        fwrite($stream, "</Towary>");
    }
}
