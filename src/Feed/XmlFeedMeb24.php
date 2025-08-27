<?php

namespace App\Feed;

use App\Feed\Writer\XmlFeedWriter;
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

        $extendOffer = Offer::getById(24657);

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

                        if($lip->getElement()->getId() == 24657)
                            $endPrice = (float)$lip->getPrice();
                    }

                    if($price * $endPrice > 0.0)
                    {
                        $data[] = $obj;
                    }
                }
            }
        }

        parent::__construct($data, function(Product|ProductSet $item) use ($offer, $extendOffer) {

            $price = 0.0;
            $endPrice = 0.0;

            foreach($item->getPrice() as $lip)
            {
                if($lip->getElement()->getId() == $offer->getId())
                    $price = (float)$lip->getPrice();

                if($lip->getElement()->getId() == $extendOffer->getId())
                    $endPrice = (float)$lip->getPrice();
            }

            $output = '<product>';
            $output .= '<id>'. $item->getId() .'</id>';
            $output .= '<sku>'. $item->getId() .'</sku>';
            $output .= '<instock>0</instock>';
            $output .= '<ean>'. $item->getEan() .'</ean>';
            $output .= '<name>'. $item->getName("pl") .'</name>';

            if($item instanceof Product)
            {
                $output .= '<serie>'. $item->getModel() .'</serie>';
            }
            else
            {
                $output .= '<serie>'. $item->getParent()->getKey() .'</serie>';
            }
            $output .= "<price>". number_format($price, 2, ".", "") .'</price>';
            $output .= "<endprice>". number_format($endPrice, 2, ".", "") .'</endprice>';
            $output .= "<currency>" . $offer->getCurrency() . "</currency>";
            $output .= "<weight>" . $item->getMass()->getValue() . "</weight>";
            $output .= "<summary></summary>";

            $output .= "<descriptionextra1><![CDATA[" . ($item->getDesc1("pl") ?? "") . "]]></descriptionextra1>";
            $output .= "<descriptionextra2><![CDATA[" . ($item->getDesc2("pl") ?? "") . "]]></descriptionextra2>";
            $output .= "<descriptionextra3><![CDATA[" . ($item->getDesc3("pl") ?? "") . "]]></descriptionextra3>";
            $output .= "<descriptionextra4><![CDATA[" . ($item->getDesc4("pl") ?? "") . "]]></descriptionextra4>";

            $output .= "<currency>" . $offer->getCurrency() . "</currency>";
            $output .= '<bundle>' . ($item instanceof Product ? 0 : 1) . '</bundle>';
            if($item instanceof ProductSet)
            {
                $output .= "<items>";
                foreach ($item->getSet() as $lis)
                {
                    $output .= "<item>";
                    $output .= "<id>" . $lis->getElement()->getId() . "</id>";
                    $output .= "<quantity>" . $lis->getQuantity() . "</quantity>";
                    $output .= "</item>";
                }
                $output .= "</items>";
            }
            $images = "<images>";
            $images .= "<image>" . $item->getImage()->getFrontendPath() . "</image>";
            foreach ($item->getImages() as $image)
            {
                $images .= "<image>" . $image->getImage()->getFrontendPath() . "</image>";
            }
            if($item instanceof Product)
            {
                foreach ($item->getPhotos() as $image)
                {
                    $images .= "<image>" . $image->getImage()->getFrontendPath() . "</image>";
                }
                foreach ($item->getImagesModel() as $image)
                {
                    $images .= "<image>" . $image->getImage()->getFrontendPath() . "</image>";
                }
            }
            $images .= "</images>";
            $output .= $images;
            $output .= "<packagecount>".$item->getPackageCount()."</packagecount>";
            $output .= "<packages>";

            if($item instanceof Product)
            {
                foreach ($item->getPackages() as $lip)
                {
                    /** @var Package $package */
                    $package = $lip->getElement();

                    $output .= "<package>";
                    $output .= "<code>" . $package->getBarcode() . "</code>";
                    $output .= "<count>" . $lip->getQuantity() . "</count>";
                    $output .= "<width>" . $package->getWidth()->getValue() / 10 . "</width>";
                    $output .= "<depth>" . $package->getDepth()->getValue() / 10 . "</depth>";
                    $output .= "<height>" . $package->getHeight()->getValue() / 10 . "</height>";
                    $output .= "<weight>" . $package->getMass()->getValue() . "</weight>";
                    $output .= "</package>";
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

                        $output .= "<package>";
                        $output .= "<code>" . $package->getBarcode() . "</code>";
                        $output .= "<count>" . $lip->getQuantity() . "</count>";
                        $output .= "<width>" . $package->getWidth()->getValue() / 10 . "</width>";
                        $output .= "<depth>" . $package->getDepth()->getValue() / 10 . "</depth>";
                        $output .= "<height>" . $package->getHeight()->getValue() / 10 . "</height>";
                        $output .= "<weight>" . $package->getMass()->getValue() . "</weight>";
                        $output .= "</package>";
                    }
                }
            }
            $output .= "</packages>";

            if($item instanceof Product)
            {
                $output .= '<width>'. $item->getWidth()->getValue() .'</width>';
                $output .= '<height>'. $item->getHeight()->getValue() .'</height>';
                $output .= '<depth>'. $item->getDepth()->getValue() .'</depth>';
                $output .= '<files>';
                foreach ($item->getDocuments() as $document)
                {
                    $output .= "<file>" . $document->getFullPath() . "</file>";
                }
                $output .= '</files>';
            }
            else
            {
                $output .= '<files></files>';
            }

            $output .= '<googlecategory>';
            if($item->getGoogleCategory())
            {
                $output .= $item->getGoogleCategory();
            }
            $output .= '</googlecategory>';
            $output .= "<parameters>";


            if($item->getParametersAllegro() && $item->getParametersAllegro()->getGroups())
            {
                $output .= "<allegro>";
                $output .= "<category>";
                $output .= $item->getParametersAllegro()->getGroups()[0]->getConfiguration()->getId();
                $output .= "</category>";
                $output .= "<parameters>";

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

                    $output .= "<parameter>";
                    $output .= "<id>" . $k->getConfiguration()->getId() . "</id>";
                    $output .= "<name>" . $cfg->getTitle() . "</name>";
                    $output .= "<value>" . $value . "</value>";
                    $output .= "<label>" . $label . "</label>";
                    $output .= "</parameter>";
                }
                $output .= "</parameters>";
                $output .= "</allegro>";
            }
            $output .= "</parameters>";
            $output .= '<manufacturer>MEGSTYL</manufacturer>';
            $output .= '</product>';

            return $output;
        });
    }
}
