<?php

declare(strict_types=1);

namespace App\Studio\Grid;

use App\Model\Grid\OfferPrice;
use App\Model\Grid\PackageDimension;
use Pimcore\Bundle\StudioBackendBundle\Grid\Column\ColumnResolverInterface;
use Pimcore\Bundle\StudioBackendBundle\Grid\Column\CoreElementColumnResolverInterface;
use Pimcore\Bundle\StudioBackendBundle\Grid\Column\ExportResolverInterface;
use Pimcore\Bundle\StudioBackendBundle\Grid\Schema\Column;
use Pimcore\Bundle\StudioBackendBundle\Grid\Schema\ColumnData;
use Pimcore\Bundle\StudioBackendBundle\Util\Constant\ElementTypes;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Service;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\UserInterface;

/**
 * Resolves the Megstyl grid columns. Parameters are encoded in the column key, so saved grid
 * configurations stay self-contained: "offerPrice_<offerId>", "packageDimension_<n>_<Dimension>".
 */
abstract class MegstylColumnResolver implements ColumnResolverInterface, CoreElementColumnResolverInterface, ExportResolverInterface
{
    public static function offerPriceKey(int $offerId): string
    {
        return 'offerPrice_' . $offerId;
    }

    public static function packageDimensionKey(int $n, string $dimension): string
    {
        return 'packageDimension_' . $n . '_' . $dimension;
    }

    public function supportedElementTypes(): array
    {
        return [ElementTypes::TYPE_OBJECT];
    }

    public function resolveForCoreElement(Column $column, ElementInterface $element): ColumnData
    {
        return new ColumnData(
            key: $column->getKey(),
            locale: $column->getLocale(),
            value: $this->value($column->getKey(), $element),
            fieldType: $this->getType(),
        );
    }

    public function resolveForExport(Column $column, ElementInterface $element, UserInterface $user): ColumnData
    {
        $value = $this->value($column->getKey(), $element);

        return new ColumnData(
            key: $column->getKey(),
            locale: $column->getLocale(),
            value: is_scalar($value) || $value === null ? (string) $value : json_encode($value),
            fieldType: $this->getType(),
        );
    }

    /**
     * Inherited values and unpublished relations included, like the classic admin grid.
     */
    public function value(string $key, ElementInterface $element): mixed
    {
        $hideUnpublished = DataObject::doHideUnpublished();
        DataObject::setHideUnpublished(false);

        try
        {
            return Service::useInheritedValues(true, fn () => $this->resolve($key, $element));
        }
        finally
        {
            DataObject::setHideUnpublished($hideUnpublished);
        }
    }

    private function resolve(string $key, ElementInterface $element): mixed
    {
        if (preg_match('/^offerPrice_(\d+)$/', $key, $m))
        {
            return OfferPrice::of($element, (int) $m[1]);
        }

        if (preg_match('/^packageDimension_(\d+)_(\w+)$/', $key, $m))
        {
            return PackageDimension::of($element, (int) $m[1], $m[2]);
        }

        return null;
    }
}
