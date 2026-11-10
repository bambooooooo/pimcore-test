<?php

namespace App\Tests\Functional\Studio;

use App\Model\Grid\OfferPrice;
use App\Model\Grid\PackageDimension;
use App\Studio\Grid\MegstylColumnResolver;
use App\Studio\Grid\OfferPriceColumnResolver;
use App\Studio\Grid\PackageDimensionColumnResolver;
use App\Tests\FunctionalTester;
use Codeception\Test\Unit;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Data\QuantityValue;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;

/**
 * Grid columns "Megstyl / Offer price" and "Megstyl / Package dimension" in Pimcore Studio
 * (classic admin grid operators Price and PackageDim).
 */
class GridColumnTest extends Unit
{
    protected FunctionalTester $tester;

    /**
     * OfferPrice and PackageDimension are called by MegstylColumnResolver, which shows unpublished relations;
     * earlier web requests in the suite leave the static flag on.
     */
    protected function _before(): void
    {
        DataObject::setHideUnpublished(false);
    }

    public function testOfferPriceOfProduct(): void
    {
        $product = $this->productWithPrices(['offer-a' => 99.5, 'offer-b' => 120.0]);

        $this->assertSame(99.5, OfferPrice::of($product, $this->offer('offer-a')->getId()));
        $this->assertSame(120.0, OfferPrice::of($product, $this->offer('offer-b')->getId()));
        $this->assertNull(OfferPrice::of($product, $this->offer('offer-c')->getId()), 'no price in other offers');
    }

    public function testOfferPriceOfOtherObjects(): void
    {
        $this->assertSame(OfferPrice::NOT_SUPPORTED, OfferPrice::of(new Package(), 10));
        $this->assertNull(OfferPrice::of($this->productWithPrices([]), 10), 'product without prices');
    }

    public function testPackageDimensionRepeatsPackagesByQuantity(): void
    {
        $product = $this->productWithPackages([[$this->package(100, 50), 2], [$this->package(30, 20), 1]]);

        $this->assertSame(100.0, PackageDimension::of($product, 1, 'Width'));
        $this->assertSame(100.0, PackageDimension::of($product, 2, 'Width'));
        $this->assertSame(30.0, PackageDimension::of($product, 3, 'Width'));
        $this->assertSame(20.0, PackageDimension::of($product, 3, 'Height'));
        $this->assertNull(PackageDimension::of($product, 4, 'Width'), 'there is no 4th package');
    }

    public function testPackageDimensionOfSetCountsPackagesOfAllProducts(): void
    {
        $chair = $this->productWithPackages([[$this->package(40, 90), 1]]);
        $chair->save();
        $table = $this->productWithPackages([[$this->package(120, 10), 2]]);
        $table->save();
        $set = new ProductSet();
        $set->setSet([$this->metadata('Set', $chair, ['Quantity' => 2]), $this->metadata('Set', $table, ['Quantity' => 1])]);

        $this->assertSame([40.0, 40.0, 120.0, 120.0, null], array_map(
            static fn (int $n) => PackageDimension::of($set, $n, 'Width'),
            [1, 2, 3, 4, 5]
        ));
    }

    public function testPackageDimensionRejectsUnknownInput(): void
    {
        $product = $this->productWithPackages([[$this->package(100, 50), 1]]);

        $this->assertNull(PackageDimension::of($product, 0, 'Width'));
        $this->assertNull(PackageDimension::of($product, 1, 'Delete'), 'only whitelisted getters may be called');
        $this->assertSame(PackageDimension::NOT_SUPPORTED, PackageDimension::of(new Offer(), 1, 'Width'));
    }

    public function testResolverReadsParametersFromColumnKey(): void
    {
        $product = $this->productWithPrices(['offer-a' => 5.0]);
        $product->setPackages([$this->metadata('Packages', $this->package(70, 60), ['Quantity' => 1])]);
        $offerId = $this->offer('offer-a')->getId();

        $this->assertSame('offerPrice_10', MegstylColumnResolver::offerPriceKey(10));
        $this->assertSame('packageDimension_1_Height', MegstylColumnResolver::packageDimensionKey(1, 'Height'));
        $this->assertSame(5.0, (new OfferPriceColumnResolver())->value("offerPrice_$offerId", $product));
        $this->assertSame(60.0, (new PackageDimensionColumnResolver())->value('packageDimension_1_Height', $product));
        $this->assertNull((new OfferPriceColumnResolver())->value('somethingElse', $product));
    }

    /** @param array<string, float> $prices offer key => price */
    private function productWithPrices(array $prices): Product
    {
        $product = $this->tester->haveDataObject('Product', published: false);
        $items = [];

        foreach ($prices as $offerKey => $price)
        {
            $items[] = $this->metadata('Price', $this->offer($offerKey), ['Price' => $price]);
        }

        $product->setPrice($items);

        return $product;
    }

    private function offer(string $key): Offer
    {
        return Offer::getByPath('/_tests/' . $key) ?? $this->tester->haveDataObject('Offer', key: $key, published: false);
    }

    private function productWithPackages(array $packages): Product
    {
        $product = $this->tester->haveDataObject('Product', published: false);
        $product->setPackages(array_map(fn (array $p) => $this->metadata('Packages', $p[0], ['Quantity' => $p[1]]), $packages));

        return $product;
    }

    private function package(float $width, float $height): Package
    {
        return $this->tester->haveDataObject('Package', [
            'Width' => new QuantityValue($width),
            'Height' => new QuantityValue($height),
        ], published: false);
    }

    private function metadata(string $field, object $element, array $data): ObjectMetadata
    {
        $item = new ObjectMetadata($field, array_keys($data), $element);
        $item->setData($data);

        return $item;
    }
}
