<?php

namespace App\Twig;

use Picqer\Barcode\BarcodeGeneratorHTML;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class SelectedLanguagesExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('selected_languages', [$this, 'getLanguages'], ['is_safe' => ['html']]),
        ];
    }

    public function getLanguages(): array
    {
        return \Pimcore\Tool::getValidLanguages();
    }
}
