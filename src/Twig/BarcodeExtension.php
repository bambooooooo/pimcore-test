<?php

namespace App\Twig;

use Picqer\Barcode\BarcodeGeneratorHTML;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class BarcodeExtension extends AbstractExtension
{
    public function __construct()
    {
        $this->generator = new BarcodeGeneratorHTML();
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('code_128', [$this, 'Code128'], ['is_safe' => ['html']]),
        ];
    }

    /**
     * Returns Code128
     *
     * @param string $code
     * @param $type
     * @param $w
     * @param $h
     * @return string
     * @throws \Picqer\Barcode\Exceptions\UnknownTypeException
     */
    public function Code128(string $code, string $type="C128", int $h=60, int $w=1)
    {
        return $this->generator->getBarcode($code, $type, $w, $h);
    }
}
