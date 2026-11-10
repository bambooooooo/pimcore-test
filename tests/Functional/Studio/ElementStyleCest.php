<?php

namespace App\Tests\Functional\Studio;

use App\Model\AdminStyle\AdminStyleResolver;
use App\Tests\FunctionalTester;
use Pimcore\Bundle\GenericDataIndexBundle\Service\SearchIndex\IndexQueue\SynchronousProcessingServiceInterface;

/**
 * Tree icons and tooltips (App\Model\AdminStyle) applied to Pimcore Studio by StudioElementStyleSubscriber.
 */
class ElementStyleCest
{
    public function studioDetailCarriesIconAndTooltip(FunctionalTester $I): void
    {
        // Studio checks permissions against the search index - index the fixture right away
        $I->grabService(SynchronousProcessingServiceInterface::class)->enable();
        $product = $I->haveDataObject('Product', ['ObjectType' => 'VIRTUAL', 'Model' => 'MOD-1'], key: 'styled-product', published: false);

        $I->amLoggedInAsPimcoreAdmin();
        $I->amOnPage('/pimcore-studio/api/data-objects/' . $product->getId());

        $I->seeResponseCodeIs(200);
        $attributes = json_decode($I->grabResponseContent(), true)['customAttributes'];
        $I->assertSame(['type' => 'path', 'value' => '/UI/square-gray.svg'], $attributes['icon']);
        $I->assertStringContainsString('<strong>styled-product</strong>', $attributes['tooltip']);
        $I->assertStringContainsString('Id: ' . $product->getId(), $attributes['tooltip']);
        $I->assertStringContainsString('MOD-1', $attributes['tooltip']);
    }

    public function classesWithoutStyleKeepStudioDefaults(FunctionalTester $I): void
    {
        $offer = $I->haveDataObject('Offer', published: false);

        $I->assertNull((new AdminStyleResolver())->resolve($offer));
    }

    /**
     * @example ["MODEL", "/UI/square-blue.svg"]
     * @example ["SKU", "/UI/square-orange.svg"]
     * @example ["ACTUAL", null]
     */
    public function productIconDependsOnObjectType(FunctionalTester $I, \Codeception\Example $example): void
    {
        $product = $I->haveDataObject('Product', ['ObjectType' => $example[0]], published: false);

        $I->assertSame($example[1], (new AdminStyleResolver())->resolve($product)->getElementIcon());
    }
}
