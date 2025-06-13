<?php

namespace App\Service;

use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\QuantityValue\Unit;

class OfferService
{
    public function getObjectPrices(Product|ProductSet $obj): array
    {
        $offers = new DataObject\Offer\Listing();
        $offers->setCondition("`published` = 1");

        $offerIds = [];
        $productOffers = [];

        foreach ($obj->getPrice() as $price) {
            if($price->getFixed())
            {
                $productOffers[] = $price;
                $offerIds[] = $price->getElement()->getId();
            }
        }

        foreach ($offers as $offer)
        {
            if(in_array($offer->getId(), $offerIds))
            {
                continue;
            }

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
                $item = new ObjectMetadata('Price', ['Price', 'Currency', 'Fixed'], $offer);
                $item->setPrice($price);
                $item->setCurrency("PLN");
                $item->setFixed(false);

                $productOffers[] = $item;
            }
        }

        return $productOffers;
    }
}
