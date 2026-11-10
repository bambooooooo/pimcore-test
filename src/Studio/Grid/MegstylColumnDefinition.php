<?php

declare(strict_types=1);

namespace App\Studio\Grid;

use Pimcore\Bundle\StudioBackendBundle\Grid\Column\ColumnDefinitionInterface;
use Pimcore\Bundle\StudioBackendBundle\Grid\Column\FrontendType;

/**
 * Read-only, exportable grid columns computed from the object (see MegstylColumnResolver).
 */
abstract class MegstylColumnDefinition implements ColumnDefinitionInterface
{
    public const OFFER_PRICE = 'megstyl.offerPrice';
    public const PACKAGE_DIMENSION = 'megstyl.packageDimension';

    public function getConfig(mixed $config): array
    {
        return [];
    }

    public function isSortable(): bool
    {
        return false;
    }

    public function isFilterable(): bool
    {
        return false;
    }

    public function getFrontendType(): string
    {
        return FrontendType::INPUT->value;
    }

    public function isExportable(): bool
    {
        return true;
    }
}
