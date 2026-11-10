<?php

declare(strict_types=1);

namespace App\Model\Grid;

use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\Element\ElementInterface;

/**
 * A dimension of the n-th package of a product (set), packages repeated by their quantity -
 * grid column "Megstyl / Package dimension". Classic admin: grid operator App\Model\Grid\PackageDim.
 */
final class PackageDimension
{
    public const DIMENSIONS = ['Width', 'Height', 'Depth', 'WidthOuter', 'HeightOuter', 'DepthOuter', 'Volume', 'Mass'];
    public const NOT_SUPPORTED = 'Object not supported';

    public static function of(ElementInterface $element, int $n, string $dimension): mixed
    {
        if ($n < 1 || !in_array($dimension, self::DIMENSIONS, true))
        {
            return null;
        }

        $packages = match (true) {
            $element instanceof Product => self::packagesOfProduct($element),
            $element instanceof ProductSet => self::packagesOfSet($element),
            default => null,
        };

        if ($packages === null)
        {
            return self::NOT_SUPPORTED;
        }

        $nth = 1;

        foreach ($packages as $package)
        {
            if ($nth++ === $n)
            {
                $value = $package->{'get' . $dimension}();

                return $value instanceof QuantityValue ? $value->getValue() : $value;
            }
        }

        return null;
    }

    /** @return iterable<Package> */
    private static function packagesOfProduct(Product $product): iterable
    {
        foreach ($product->getPackages() as $item)
        {
            for ($i = 0; $i < (int) $item->getQuantity(); $i++)
            {
                yield $item->getElement();
            }
        }
    }

    /** @return iterable<Package> */
    private static function packagesOfSet(ProductSet $set): iterable
    {
        foreach ($set->getSet() as $item)
        {
            for ($i = 0; $i < (int) $item->getQuantity(); $i++)
            {
                yield from self::packagesOfProduct($item->getElement());
            }
        }
    }
}
