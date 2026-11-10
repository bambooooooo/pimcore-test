<?php

declare(strict_types=1);

namespace App\Model\Grid;

use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\Element\ElementInterface;

/**
 * Price of a product (set) in a given offer - grid column "Megstyl / Offer price".
 * Classic admin: grid operator App\Model\Grid\Price.
 */
final class OfferPrice
{
    public const NOT_SUPPORTED = 'Object not supported';

    public static function of(ElementInterface $element, int $offerId): mixed
    {
        if (!$element instanceof Product && !$element instanceof ProductSet)
        {
            return self::NOT_SUPPORTED;
        }

        $price = null;

        foreach ($element->getPrice() as $item)
        {
            if ($item->getElement()?->getId() === $offerId)
            {
                $price = $item->getPrice();
            }
        }

        return $price;
    }
}
