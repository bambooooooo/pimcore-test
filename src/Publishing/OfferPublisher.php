<?php

namespace App\Publishing;

use App\Service\BaselinkerService;
use App\Service\PrestashopService;
use App\Service\SubiektGTService;
use Pimcore\Model\DataObject\Offer;
use SimpleXMLElement;

class OfferPublisher
{
    public function __construct(private readonly SubiektGTService $subiektGTService)
    {

    }

    public function publish(Offer $offer)
    {
        if("TEMP-PROD-FIX" == "off")
        {
            $this->subiektGTService->request("POST", "prices", [
                'Code' => "" . $offer->getId(),
                'Name' => $offer->getKey(),
                'Brutto' => $offer->getBrutto() ?? false,
            ]);
        }
    }
}
