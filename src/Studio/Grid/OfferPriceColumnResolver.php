<?php

declare(strict_types=1);

namespace App\Studio\Grid;

final class OfferPriceColumnResolver extends MegstylColumnResolver
{
    public function getType(): string
    {
        return MegstylColumnDefinition::OFFER_PRICE;
    }
}
