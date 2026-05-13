<?php

namespace App\Publishing;

use App\Service\BaselinkerService;
use App\Service\PrestashopService;
use App\Service\SubiektGTService;
use Pimcore\Model\DataObject\Offer;
use SimpleXMLElement;

class OfferPublisher
{
    public function __construct(private readonly BaselinkerService $baselinkerService,
                                private readonly SubiektGTService $subiektGTService,
                                private readonly PrestashopService $prestashopService)
    {

    }

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

        if($offer->getPricings() && $offer->getPs_megstyl_pl())
        {
            $this->sendPsPriceGroup($offer);
        }

        if("TEMP-PROD-FIX" == "off")
        {
            $this->subiektGTService->request("POST", "prices", [
                'Code' => "" . $offer->getId(),
                'Name' => $offer->getKey(),
                'Brutto' => $offer->getBrutto() ?? false,
            ]);
        }
    }

    private function sendPsPriceGroup(Offer $offer)
    {
        if($offer->getps_megstyl_pl())
        {
            if($offer->getPs_megstyl_pl_id() && $offer->getPs_megstyl_pl_id() > 0)
            {
                $this->updatePsPriceGroup($offer);
            }
            else
            {
                $this->addPsPriceGroup($offer);
            }
        }
    }

    private function addPsPriceGroup(Offer $offer)
    {
        $xml = new SimpleXMLElement("<prestashop xmlns:xlink=\"http://www.w3.org/1999/xlink\"></prestashop>");
        $g = $xml->addChild("group");
        $g->addChild("reduction", 0);
        $g->addChild("price_display_method", $offer->getBrutto() ? 0 : 1);
        $g->addChild("show_prices", 1);
        $g->addChild("date_add", date('Y-m-d H:i:s'));
        $g->addChild("date_upd", date('Y-m-d H:i:s'));
        $name = $g->addChild("name");
        $pl = $name->addChild("language", $offer->getName("pl"));
        $pl->addAttribute("id", 1);
        $en = $name->addChild("language", $offer->getName("en"));
        $en->addAttribute("id", 2);

        $res = $this->prestashopService->post('groups', $xml);

        $offer->setPs_megstyl_pl_id((int)$res->group->id);
        $offer->save(["skip" => "3rd party integration"]);
    }

    private function updatePsPriceGroup(Offer $offer)
    {
        $xml = new SimpleXMLElement("<prestashop xmlns:xlink=\"http://www.w3.org/1999/xlink\"></prestashop>");
        $g = $xml->addChild("group");
        $g->addChild("id", $offer->getPs_megstyl_pl_id());
        $g->addChild("reduction", 0);
        $g->addChild("price_display_method", $offer->getBrutto() ? 0 : 1);
        $g->addChild("show_prices", 1);
        $g->addChild("date_add", date('Y-m-d H:i:s'));
        $g->addChild("date_upd", date('Y-m-d H:i:s'));
        $name = $g->addChild("name");
        $pl = $name->addChild("language", $offer->getName("pl"));
        $pl->addAttribute("id", 1);
        $en = $name->addChild("language", $offer->getName("en"));
        $en->addAttribute("id", 2);

        $res = $this->prestashopService->patch('groups/' . $offer->getPs_megstyl_pl_id(), $xml);
    }

}
