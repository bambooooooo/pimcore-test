<?php

namespace App\Feed;

use App\Feed\Writer\CsvFeedWriter;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

class CsvNoweKolory extends CsvFeedWriter
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

        echo 'Found: ' . count($data) . ' items. ' . PHP_EOL;

        parent::__construct($data, function (Product|ProductSet $item) use ($offer) {

            $currency = 'PLN';

            $priceBG = 0.0;
            $priceHU = 0.0;
            $priceRO = 0.0;

            foreach($item->getPrice() as $lip)
            {
                if($lip->getElement()->getKey() == "eMAG - BG")
                {
                    $priceBG = (float)$lip->getPrice();
                }

                if($lip->getElement()->getKey() == "eMAG - HU")
                {
                    $priceHU = (float)$lip->getPrice();
                }

                if($lip->getElement()->getKey() == "eMAG - RO")
                {
                    $priceRO = (float)$lip->getPrice();
                }
            }

            if($priceBG == 0.0 && $priceRO == 0.0 && $priceHU == 0.0)
            {
                return null;
            }

            $imgCount = 32;
            $images = [];

            foreach($item->getImages() as $image)
            {
                if(count($images) >= $imgCount)
                    break;

                $images[] = $image->getImage()->getFrontendPath();
            }

            if($item instanceof Product)
            {
                foreach ($item->getPhotos() as $image)
                {
                    if(count($images) >= $imgCount)
                        break;

                    $images[] = $image->getImage()->getFrontendPath();
                }
                foreach ($item->getImagesModel() as $image)
                {
                    if(count($images) >= $imgCount)
                        break;

                    $images[] = $image->getImage()->getFrontendPath();
                }
            }

            for($i=count($images); $i < $imgCount; $i++)
            {
                $images[] = "";
            }

            $fields = array_merge([
                $item->getId(),
                $item->getId(),
                '',
                '',
                $item->getEan(),
                $priceBG,
                $priceHU,
                $priceRO,
                '',
                '23.0',
                1,
                $currency,
                $item->getStock(),
                '',
                '',
                $item->getName("pl"),
                'MEGSTYL',
                $this->csvEscapeCell($item?->getDesc1()),
                $this->csvEscapeCell($item?->getDesc2()),
                $this->csvEscapeCell($item?->getDesc3()),
                $this->csvEscapeCell($item?->getDesc4()),
                $item->getImage()
            ],
                $images,
            [
                '',
                'PL'
            ]);

            return join(';', $fields) . PHP_EOL;
        });
    }

    public function begin($stream): void
    {
        $separator = ';';

        $imgCount = 32;
        $images = [];

        for($i = 1; $i < $imgCount + 1; $i++)
        {
            $images[] = "other_image_url" . $i;
        }

        $cols = array_merge([
            'part_number',
            'ext_id',
            'category_id',
            'category_name',
            'ean',
            'sale_price_bg',
            'sale_price_hu',
            'sale_price_ro',
            'original_sale_price',
            'vat_rate',
            'status',
            'offer_currency',
            'stock',
            'min_sale_price',
            'max_sale_price',
            'name',
            'brand',
            'description1',
            'description2',
            'description3',
            'description4',
            'main_image_url'
        ],
            $images,
        [
            'extra_details',
            'translation_locale'
        ]);

        fwrite($stream, "\xEF\xBB\xBF"); // BOM for Excel compatibility
        fwrite($stream, join($separator, $cols) . PHP_EOL);
    }

    function csvEscapeCell(string|null $html): string
    {
        if(!$html)
            return "";

        // Double the double-quotes
        $escaped = str_replace('"', '""', $html);

        // Wrap with quotes so commas/newlines won't break the CSV
        return '"' . $escaped . '"';
    }
}
