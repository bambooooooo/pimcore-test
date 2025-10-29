<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class IsNumericExtension extends AbstractExtension
{
    public function getTests()
    {
        return [
            new TwigTest('numeric', [$this, 'isNumeric']),
        ];
    }

    public function isNumeric($value): bool
    {
        return is_numeric($value);
    }
}
