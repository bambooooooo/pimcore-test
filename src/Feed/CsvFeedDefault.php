<?php

namespace App\Feed;

use App\Feed\Writer\CsvFeedWriter;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class CsvFeedDefault extends CsvFeedWriter
{
    public function __construct(Offer $offer, Offer $referenceOffer = null)
    {
        $refs = $offer->getDependencies()->getRequiredBy();
        $data = [];

        foreach ($refs as $ref) {
            if($ref['type'] == 'object') {
                $obj = DataObject::getById($ref['id']);
                if(($obj instanceof Product || $obj instanceof ProductSet) && (in_array($obj->getStatus(), ['Active', 'Sale']))) {
                    $data[] = $obj;
                }
            }
        }

        echo 'Found: ' . count($data) . ' items. ' . PHP_EOL;

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

            if($price == 0.0)
            {
                return "";
            }

            $bundleItems = [];
            $bundleText = "";
            if($item instanceof ProductSet)
            {
                foreach ($item->getSet() as $lis)
                {
                    $bundleItems[] = $lis->getObject()->getId() . "|" . $lis->getQuantity();
                }

                $bundleText = join(",", $bundleItems);
            }


            $fields = [
                $item->getId(),
                $item->getStock(),
                $item->getEan(),
                $item->getName("pl"),
                ($item instanceof Product) ? $item->getModel() : $item->getParent()->getKey(),
                $price,
                $endPrice,
                $offer->getCurrency(),
                ($item instanceof Product) ? round($item->getWidth()->getValue() / 10) : '',
                ($item instanceof Product) ? round($item->getHeight()->getValue() / 10) : '',
                ($item instanceof Product) ? round($item->getDepth()->getValue() / 10) : '',
                $item->getMass()->getValue(),
                $item instanceof Product ? 0 : 1,
                $bundleText,
                $item->getPackageCount(),
            ];

            if($item instanceof Product)
            {
                $i=1;
                foreach($item->getPackages() as $lip)
                {
                    /** @var Package $package */
                    $package = $lip->getElement();

                    $fields[] = $package->getBarcode();
                    $fields[] = $lip->getQuantity();
                    $fields[] = round($package->getWidth()->getValue() / 10);
                    $fields[] = round($package->getDepth()->getValue() / 10);
                    $fields[] = round($package->getHeight()->getValue() / 10);
                    $fields[] = $package->getMass()->getValue();

                    $i++;
                }

            }
            else
            {
                $i=1;
                foreach ($item->getSet() as $li)
                {
                    foreach ($li->getElement()->getPackages() as $lip)
                    {
                        /** @var Package $package */
                        $package = $lip->getElement();

                        $fields[] = $package->getBarcode();
                        $fields[] = $li->getQuantity() * $lip->getQuantity();
                        $fields[] = round($package->getWidth()->getValue() / 10);
                        $fields[] = round($package->getDepth()->getValue() / 10);
                        $fields[] = round($package->getHeight()->getValue() / 10);
                        $fields[] = $package->getMass()->getValue();

                        $i++;
                    }
                }
            }

            for($j = $i; $j < 13; $j++)
            {
                $fields[] = '';
                $fields[] = '';
                $fields[] = '';
                $fields[] = '';
                $fields[] = '';
                $fields[] = '';
            }

            $fields[] = $item->getImage()->getFrontendPath();
            $imgCount = 1;

            foreach ($item->getImages() as $image)
            {
                $fields[] = $image->getImage()->getFrontendPath();
                $imgCount++;
            }
            if($item instanceof Product)
            {
                foreach ($item->getPhotos() as $image)
                {
                    $fields[] = $image->getImage()->getFrontendPath();
                    $imgCount++;
                }
                foreach ($item->getImagesModel() as $image)
                {
                    $fields[] = $image->getImage()->getFrontendPath();
                    $imgCount++;
                }
            }

            for($j = $imgCount; $j < 21; $j++)
            {
                $fields[] = '';
            }

            $fields[] = 'MEGSTYL';
            $fields[] = ($item instanceof Product && $item->getInstruction()) ? $item->getInstruction()->getFrontendPath() : "";

            $fields[] = $this->csvEscapeCell($item->getDesc1("pl"));
            $fields[] = $this->csvEscapeCell($item->getDesc2("pl"));
            $fields[] = $this->csvEscapeCell($item->getDesc3("pl"));
            $fields[] = $this->csvEscapeCell($item->getDesc4("pl"));

            return join(';', $fields) . PHP_EOL;
        });
    }

    function begin($stream): void
    {
        $separator = ";";

        $cols = [
            'id',
            'instock',
            'ean',
            'name',
            'serie',
            'price',
            'endprice',
            'currency',
            'width',
            'height',
            'depth',
            'weight',
            'bundle',
            'bundleitems',
            'packagecount',
        ];

        for($i = 1; $i < 13; $i++)
        {
            $cols[] = 'package' . $i . "code";
            $cols[] = 'package' . $i . "count";
            $cols[] = 'package' . $i . "width";
            $cols[] = 'package' . $i . "height";
            $cols[] = 'package' . $i . "length";
            $cols[] = 'package' . $i . "weight";
        }

        for($i = 1; $i < 21; $i++)
        {
            $cols[] = 'image' . $i . "code";
        }

        $cols = array_merge($cols, [
            'manufacturer',
            'manual',
            'description1pl',
            'description2pl',
            'description3pl',
            'description4pl'
        ]);

        fwrite($stream, "\xEF\xBB\xBF");
        fwrite($stream, join($separator, $cols) . PHP_EOL);
    }

    function csvEscapeCell(string|null $html): string
    {
        if(!$html)
            return "";

        $escaped = str_replace('"', '""', $html);

        return '"' . $escaped . '"';
    }
}
