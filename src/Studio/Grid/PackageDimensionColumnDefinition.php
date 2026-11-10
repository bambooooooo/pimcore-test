<?php

declare(strict_types=1);

namespace App\Studio\Grid;

final class PackageDimensionColumnDefinition extends MegstylColumnDefinition
{
    public function getType(): string
    {
        return self::PACKAGE_DIMENSION;
    }
}
