<?php

namespace App\Tests\Functional;

use App\Tests\FunctionalTester;
use Pimcore\Model\DataObject\Product;

/**
 * Stock sync endpoints used by external systems (X-Api-Token).
 */
class StockApiCest
{
    public function updatesStockOfSingleProduct(FunctionalTester $I): void
    {
        $product = $I->haveDataObject('Product', published: false);

        $I->haveValidApiToken();
        $I->amOnPage("/objects/stocks/{$product->getId()}/7");

        $I->seeResponseCodeIs(200);
        $I->see('Ok');
        $I->assertSame('7', Product::getById($product->getId(), ['force' => true])->getStock());
    }

    public function stockCanBeSetToZero(FunctionalTester $I): void
    {
        $product = $I->haveDataObject('Product', published: false);
        $product->setStock(5);

        $I->haveValidApiToken();
        $I->amOnPage("/objects/stocks/{$product->getId()}/0");

        $I->seeResponseCodeIs(200);
        $I->assertSame('0', Product::getById($product->getId(), ['force' => true])->getStock());
    }

    public function rejectsObjectsThatAreNotProducts(FunctionalTester $I): void
    {
        $folder = $I->haveDataObject('Offer', published: false);

        $I->haveValidApiToken();
        $I->amOnPage("/objects/stocks/{$folder->getId()}/7");

        $I->seeResponseCodeIs(400);
        $I->see('Object not found');
    }

    public function bulkUpdateReportsFoundChangedAndSkipped(FunctionalTester $I): void
    {
        $changed = $I->haveDataObject('Product', published: false);
        $unchanged = $I->haveDataObject('Product', published: false);
        $unchanged->setStock(3);

        $I->haveValidApiToken();
        $I->sendJson('POST', '/objects/stocks', [
            $changed->getId() => 12,
            $unchanged->getId() => 3,
            999999999 => 1,
        ]);

        $I->seeResponseCodeIs(200);
        $I->assertSame('Ok. Found: 2, Changed: 1, Skipped: 1', $I->grabResponseContent());
        $I->assertSame('12', Product::getById($changed->getId(), ['force' => true])->getStock());
        $I->assertSame('3', Product::getById($unchanged->getId(), ['force' => true])->getStock());
    }

    public function anonymousBulkUpdateChangesNothing(FunctionalTester $I): void
    {
        $product = $I->haveDataObject('Product', published: false);
        $product->setStock(4);

        $I->sendJson('POST', '/objects/stocks', [$product->getId() => 99]);

        $I->seeResponseCodeIs(401);
        $I->assertSame('4', Product::getById($product->getId(), ['force' => true])->getStock());
    }
}
