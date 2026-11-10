<?php

namespace App\Tests\Unit\Twig;

use App\Twig\BarcodeExtension;
use App\Twig\IsNumericExtension;
use Codeception\Test\Unit;
use Twig\Environment;
use Twig\Loader\ArrayLoader;

/**
 * Twig helpers used by the factory printouts and labels.
 */
class TwigExtensionTest extends Unit
{
    public function testNumericTest(): void
    {
        $html = $this->render('{{ a is numeric ? "y" : "n" }}{{ b is numeric ? "y" : "n" }}{{ c is numeric ? "y" : "n" }}', [
            'a' => '12.5', 'b' => 'abc', 'c' => 7,
        ]);

        $this->assertSame('yny', $html);
    }

    public function testCode128RendersBarcodeHtml(): void
    {
        $html = $this->render('{{ code_128("5901234123457") }}');

        $this->assertStringContainsString('<div', $html, 'barcode must not be escaped');
        $this->assertStringContainsString('height:60px', str_replace(' ', '', $html));
    }

    public function testCode128HonoursHeight(): void
    {
        $html = $this->render('{{ code_128("123456", "C128", 30) }}');

        $this->assertStringContainsString('height:30px', str_replace(' ', '', $html));
    }

    private function render(string $template, array $context = []): string
    {
        $twig = new Environment(new ArrayLoader(['t' => $template]));
        $twig->addExtension(new IsNumericExtension());
        $twig->addExtension(new BarcodeExtension());

        return $twig->render('t', $context);
    }
}
