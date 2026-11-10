<?php

namespace App\Tests\Unit\Model\Grid;

use App\Model\Grid\Constant;
use App\Model\Grid\Round;
use Codeception\Test\Unit;
use Pimcore\Bundle\AdminBundle\DataObject\GridColumnConfig\Operator\OperatorInterface;
use Pimcore\Model\DataObject\Data\QuantityValue;

/**
 * Custom grid column operators used in the admin product grids.
 */
class GridOperatorTest extends Unit
{
    public function testConstantReturnsConfiguredNumber(): void
    {
        $result = (new Constant($this->config('12.5')))->getLabeledValue([]);

        $this->assertSame('const', $result->getLabel());
        $this->assertSame(12.5, $result->getValue());
    }

    /**
     * @dataProvider roundings
     */
    public function testRoundFormatsNumbers(string $precision, mixed $input, string $expected): void
    {
        $this->assertSame($expected, (new Round($this->config($precision)))->format($input));
    }

    public static function roundings(): array
    {
        return [
            'integer precision' => ['0', 12.5, '13'],
            'two decimals' => ['2', '3.14159', '3.14'],
            'numeric string' => ['1', '10', '10'],
            'non numeric' => ['2', 'abc', ''],
            'null' => ['2', null, ''],
        ];
    }

    public function testRoundFormatsQuantityValues(): void
    {
        $round = new Round($this->config('1'));

        $this->assertSame('2.3', $round->format(new QuantityValue(2.345)));
        $this->assertSame('', $round->format(new QuantityValue(null)));
    }

    public function testRoundAppliesToChildOperatorValue(): void
    {
        $round = new Round($this->config('2', [$this->child(19.999)]));

        $result = $round->getLabeledValue([]);

        $this->assertSame('20', $result->value);
        $this->assertSame('Rounded', $result->label);
    }

    public function testRoundAppliesToEveryValueOfArrayChild(): void
    {
        $round = new Round($this->config('0', [$this->child([[1.4, 2.6]], true)]));

        $result = $round->getLabeledValue([]);

        $this->assertTrue($result->isArrayType);
        $this->assertSame([['1', '3']], $result->value);
    }

    public function testRoundWithoutChildrenReturnsNull(): void
    {
        $this->assertNull((new Round($this->config('2')))->getLabeledValue([])->value);
    }

    private function config(string $additionalData, array $children = []): \stdClass
    {
        return (object) ['label' => 'Rounded', 'children' => $children, 'additionalData' => $additionalData];
    }

    private function child(mixed $value, bool $isArrayType = false): OperatorInterface
    {
        $child = $this->createMock(OperatorInterface::class);
        $child->method('getLabeledValue')->willReturn((object) ['value' => $value, 'isArrayType' => $isArrayType]);

        return $child;
    }
}
