<?php

namespace App\Feed;

use App\Feed\Writer\XmlFeedWriter;
use DOMDocument;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class XmlFeedMeb24 extends XmlFeedWriter
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

        parent::__construct($data, function(Product|ProductSet $item) use ($offer, $referenceOffer) {

            $price = 0.0;
            $endPrice = 0.0;

            foreach($item->getPrice() as $lip)
            {
                if($lip->getElement()->getId() == $offer->getId())
                    $price = (float)$lip->getPrice();

                if($lip->getElement()->getId() == $referenceOffer->getId())
                    $endPrice = (float)$lip->getPrice();
            }

            $doc = new DOMDocument('1.0', 'utf-8');
            $doc->formatOutput = true;
            $prod = $doc->createElement('product');
            $prod->setAttribute('id', $item->getId());
            $prod->appendChild($doc->createElement('sku', (string)$item->getId()));
            $prod->appendChild($doc->createElement('instock', (string)$item->getStock()));
            $prod->appendChild($doc->createElement('ean', (string)$item->getName("pl")));
            $prod->appendChild($doc->createElement('name', (string)$item->getId()));

            if($item instanceof Product)
            {
                $prod->appendChild($doc->createElement('serie', (string)$item->getModel()));
            }
            else
            {
                $prod->appendChild($doc->createElement('serie', (string)$item->getParent()->getKey()));
            }


            $prod->appendChild($doc->createElement('price', (string)number_format($price, 2, ".", "")));
            $prod->appendChild($doc->createElement('endprice', (string)number_format($endPrice, 2, ".", "")));

            $prod->appendChild($doc->createElement('currency', (string)$offer->getCurrency()));
            $prod->appendChild($doc->createElement('weight', (string)$item->getMass()->getValue()));

            $descriptionextra1 = $doc->createElement('descriptionextra1');
            $descriptionextra1->appendChild($doc->createCDATASection($item->getDesc1("pl") ?? ""));
            $prod->appendChild($descriptionextra1);

            $descriptionextra2 = $doc->createElement('descriptionextra2');
            $descriptionextra2->appendChild($doc->createCDATASection($item->getDesc2("pl") ?? ""));
            $prod->appendChild($descriptionextra2);

            $descriptionextra3 = $doc->createElement('descriptionextra3');
            $descriptionextra3->appendChild($doc->createCDATASection($item->getDesc3("pl") ?? ""));
            $prod->appendChild($descriptionextra3);

            $descriptionextra4 = $doc->createElement('descriptionextra4');
            $descriptionextra4->appendChild($doc->createCDATASection($item->getDesc4("pl") ?? ""));
            $prod->appendChild($descriptionextra4);

            $prod->appendChild($doc->createElement('bundle', (string)($item instanceof Product ? 0 : 1)));

            if($item instanceof ProductSet)
            {
                $items = $doc->createElement("items");

                foreach ($item->getSet() as $lis)
                {
                    $it = $doc->createElement('item');
                    $it->appendChild($doc->createElement('id', $lis->getElement()->getId()));
                    $it->appendChild($doc->createElement('quantity', $lis->getQuantity()));
                    $items->appendChild($it);
                }

                $prod->appendChild($items);
            }

            $images = $doc->createElement("images");
            $images->appendChild($doc->createElement('image', $item->getImage()->getFrontendPath()));

            foreach ($item->getImages() as $image)
            {
                $images->appendChild($doc->createElement('image', $item->getImage()->getFrontendPath()));
            }
            if($item instanceof Product)
            {
                foreach ($item->getPhotos() as $image)
                {
                    $images->appendChild($doc->createElement('image', $item->getImage()->getFrontendPath()));
                }
                foreach ($item->getImagesModel() as $image)
                {
                    $images->appendChild($doc->createElement('image', $item->getImage()->getFrontendPath()));
                }
            }
            $prod->appendChild($images);

            $packages = $doc->createElement("packages");
            $prod->appendChild($doc->createElement('count', $item->getPackageCount()));

            if($item instanceof Product)
            {
                foreach ($item->getPackages() as $lip)
                {
                    /** @var Package $package */
                    $package = $lip->getElement();

                    $p = $doc->createElement('package');
                    $p->appendChild($doc->createElement('code', $package->getBarcode()));
                    $p->appendChild($doc->createElement('count', $lip->getQuantity()));
                    $p->appendChild($doc->createElement('width', $package->getWidth()->getValue() / 10));
                    $p->appendChild($doc->createElement('depth', $package->getDepth()->getValue() / 10));
                    $p->appendChild($doc->createElement('height', $package->getHeight()->getValue() / 10));
                    $p->appendChild($doc->createElement('weight', $package->getMass()->getValue()));

                    $packages->appendChild($p);
                }
            }
            else
            {
                foreach ($item->getSet() as $li)
                {
                    foreach ($li->getElement()->getPackages() as $lip)
                    {
                        /** @var Package $package */
                        $package = $lip->getElement();

                        $p = $doc->createElement('package');
                        $p->appendChild($doc->createElement('code', $package->getBarcode()));
                        $p->appendChild($doc->createElement('count', $lip->getQuantity()));
                        $p->appendChild($doc->createElement('width', $package->getWidth()->getValue() / 10));
                        $p->appendChild($doc->createElement('depth', $package->getDepth()->getValue() / 10));
                        $p->appendChild($doc->createElement('height', $package->getHeight()->getValue() / 10));
                        $p->appendChild($doc->createElement('weight', $package->getMass()->getValue()));

                        $packages->appendChild($p);
                    }
                }
            }

            $prod->appendChild($packages);

            if($item instanceof Product)
            {
                $prod->appendChild($doc->createElement('width', $item->getWidth()->getValue() / 10));
                $prod->appendChild($doc->createElement('height', $item->getHeight()->getValue() / 10));
                $prod->appendChild($doc->createElement('depth', $item->getHeight()->getValue() / 10));

                $files = $doc->createElement("files");
                foreach ($item->getDocuments() as $document)
                {
                    $files->appendChild($doc->createElement('file', $document->getFullPath()));
                }

                $prod->appendChild($files);
            }

            $prod->appendChild($doc->createElement('googlecategory', $item->getGoogleCategory()));

            $parameters = $doc->createElement("parameters");

            if($item->getParametersAllegro() && $item->getParametersAllegro()->getGroups())
            {
                $allegro = $doc->createElement("allegro");
                $allegro->appendChild($doc->createElement('category', $item->getParametersAllegro()->getGroups()[0]->getConfiguration()->getId()));

                $allegroParameters = $doc->createElement("parameters");


                foreach ($item->getParametersAllegro()->getGroups()[0]->getKeys() as $k)
                {
                    $cfg = $k->getConfiguration();

                    $value = ($cfg->getType() == 'select') ? explode("_", $k->getValue())[0] : $k->getValue();

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

                    $parameter = $doc->createElement("parameter");
                    $parameter->appendChild($doc->createElement('id', $k->getConfiguration()->getId()));
                    $parameter->appendChild($doc->createElement('name', $cfg->getTitle()));
                    $parameter->appendChild($doc->createElement('value', $value));
                    $parameter->appendChild($doc->createElement('label', $label));

                    $allegroParameters->appendChild($parameter);
                }

                $allegro->appendChild($allegroParameters);
                $parameters->appendChild($allegro);
            }

            $prod->appendChild($doc->createElement('manufacturer', 'MEGSTYL'));

            return $doc->saveXML($prod);
        });
    }
}
