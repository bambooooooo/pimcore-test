<?php

namespace App\Publishing;

use App\Service\BaselinkerService;
use App\Service\SubiektGTService;
use Pimcore\Model\DataObject\Offer;

class OfferPublisher
{
    public function __construct(private readonly BaselinkerService $baselinkerService, private readonly SubiektGTService $subiektGTService){}

    public function publish(Offer $offer)
    {
        if($offer->getPricings())
        {
            foreach ($offer->getPricings() as $pricing) {
                assert($pricing->getPublished(), "Offer's pricings should be published");
            }
        }

        if($offer->getPricings() && $offer->getBaselinker())
        {
            $this->baselinkerService->updatePriceGroup($offer);
        }

        $this->subiektGTService->request("POST", "prices", [
            'Code' => "" . $offer->getId(),
            'Name' => $offer->getKey(),
        ]);
    }
}
