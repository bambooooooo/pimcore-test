<?php

namespace App\Tests\Functional;

use App\Tests\FunctionalTester;
use Codeception\Example;

/**
 * The classic admin UI is gone (Pimcore 2026) - its bookmarks and deeplinks lead to Pimcore Studio.
 */
class LegacyAdminCest
{
    /**
     * @example ["object_123_object", "/pimcore-studio/data-object/123"]
     * @example ["object_7_folder", "/pimcore-studio/data-object/7"]
     * @example ["asset_55_image", "/pimcore-studio/asset/55"]
     * @example ["document_1_page", "/pimcore-studio/document/1"]
     * @example ["garbage", "/pimcore-studio/"]
     */
    public function deeplinksOpenTheElementInStudio(FunctionalTester $I, Example $example): void
    {
        $I->stopFollowingRedirects();
        $I->amOnPage('/admin/login/deeplink?' . $example[0]);

        $I->seeResponseCodeIs(301);
        $I->assertSame($example[1], $I->grabResponseHeader('Location'));
    }

    public function studioServesTheDeeplinkTarget(FunctionalTester $I): void
    {
        $I->amOnPage('/pimcore-studio/data-object/123');

        $I->seeResponseCodeIs(200);
    }
}
