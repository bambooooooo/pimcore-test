<?php

declare(strict_types=1);

namespace App\Studio\Grid;

final class PackageDimensionColumnResolver extends MegstylColumnResolver
{
    public function getType(): string
    {
        return MegstylColumnDefinition::PACKAGE_DIMENSION;
    }
}
