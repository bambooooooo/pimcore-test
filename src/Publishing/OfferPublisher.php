<?php

namespace App\Publishing;

use Pimcore\Model\DataObject\Offer;

class OfferPublisher
{
    public function publish(Offer $offer)
    {
        if($offer->getPricings())
        {
            foreach ($offer->getPricings() as $pricing) {
                assert($pricing->getPublished(), "Offer's pricings should be published");
            }
        }
    }
}
