<?php

namespace App\Publishing;

use App\Service\BaselinkerService;
use App\Service\SubiektGTService;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\User;

class UserPublisher
{
    public function __construct(private readonly SubiektGTService $subiektGTService){}

    public function publish(User $user): void
    {
        if($user->getVAT() && $user->getOffers())
        {
            $baseOffer = $user->getOffers()[0];

            $this->subiektGTService->request("POST", "users", [
                'Name' => $user->getName(),
                'VAT' => $user->getVAT(),
                'PriceLevel' => "".$baseOffer->getId()
            ]);
        }
    }
}
