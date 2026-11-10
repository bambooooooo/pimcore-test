<?php

declare(strict_types=1);

namespace App\Studio\Grid;

final class OfferPriceColumnDefinition extends MegstylColumnDefinition
{
    public function getType(): string
    {
        return self::OFFER_PRICE;
    }
}
