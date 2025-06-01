<?php

namespace App\Service;

use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\QuantityValue\Unit;

class OfferService
{
    public function getObjectOffers(Product|ProductSet $obj): array
    {
        $offers = new DataObject\Offer\Listing();
        $offers->setCondition("`published` = 1");

        $productOffers = [];

        foreach ($offers as $offer)
        {
            $price = null;

            foreach($offer->getPricings() as $offerPricing)
            {
                foreach($obj->getPricing() as $productPricing)
                {
                    if($offerPricing == $productPricing->getElement() && !$price)
                    {
                        $price = $productPricing->getPrice();
                    }
                }
            }

            if($price)
            {
                $PLN = Unit::getById("PLN");
                $rel = new DataObject\Data\BlockElement("offer", "manyToOneRelation", $offer);
                $p = new DataObject\Data\BlockElement("price", "quantityValue", new QuantityValue($price, $PLN));

                $data = [
                    "Offer" => $rel,
                    "Price" => $p,
                ];

                $productOffers[] = $data;
            }
        }

        return $productOffers;
    }
}
