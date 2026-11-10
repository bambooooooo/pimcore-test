<?php

namespace App\Tests\Functional\Studio;

use App\Tests\FunctionalTester;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\ObjectMetadata;

/**
 * Backend of the Megstyl Pimcore Studio plugin (assets/studio): toolbar context and "Add children".
 */
class StudioExtensionCest
{
    public function productContextCarriesQualityAndEan(FunctionalTester $I): void
    {
        $product = $I->haveDataObject('Product', ['ObjectType' => 'ACTUAL', 'Ean' => '5901234123457'], published: false);

        $context = $this->toolbar($I, $product->getId());

        $I->assertSame('Product', $context['className']);
        $I->assertIsInt($context['quality'], 'Quality is a calculated value');
        $I->assertSame((int) $product->getQuality(), $context['quality']);
        $I->assertTrue($context['hasEan']);
        $I->assertFalse($context['published']);
        $I->assertContains('pl', $context['websiteLanguages']);
    }

    public function qualityIsOnlyShownForActualProducts(FunctionalTester $I): void
    {
        $model = $I->haveDataObject('Product', ['ObjectType' => 'MODEL'], published: false);
        $set = $I->haveDataObject('ProductSet', published: false);

        $I->assertNull($this->toolbar($I, $model->getId())['quality']);
        $I->assertNull($this->toolbar($I, $set->getId())['quality'], 'a set without products has no quality');
        $I->assertFalse($this->toolbar($I, $model->getId())['hasEan']);
    }

    public function groupCountsProductsAndSets(FunctionalTester $I): void
    {
        $group = $I->haveDataObject('Group', published: false);
        $I->haveDataObject('Product', ['Groups' => [$group]], published: false);
        $I->haveDataObject('ProductSet', ['Groups' => [$group]], published: false);

        $I->assertSame(2, $this->toolbar($I, $group->getId())['productCount'], 'unpublished relations count, like in the classic admin');
    }

    public function userListsOffers(FunctionalTester $I): void
    {
        $offers = [$I->haveDataObject('Offer', published: false), $I->haveDataObject('Offer', published: false)];
        $user = $I->haveDataObject('User', ['Offers' => $offers], published: false);

        $I->assertSame([$offers[0]->getId(), $offers[1]->getId()], $this->toolbar($I, $user->getId())['offers']);
    }

    public function orderWarnsBeforeOptimikExport(FunctionalTester $I): void
    {
        // publishing a product needs a valid group, prices, images... - the check only reads the flag
        $ok = $I->haveDataObject('Product', ['OptimikExportSerieSize' => 5], key: 'serie-ok', published: false);
        $partial = $I->haveDataObject('Product', ['OptimikExportSerieSize' => 5], key: 'serie-partial', published: false);
        $draft = $I->haveDataObject('Product', key: 'draft', published: false);
        $this->markPublished($ok, $partial);

        $order = $I->haveDataObject('Order', ['Products' => [
            $this->item('Products', $ok, ['Quantity' => 10]),
            $this->item('Products', $partial, ['Quantity' => 7]),
            $this->item('Products', $draft, ['Quantity' => 1]),
        ]], published: false);

        $warnings = $this->toolbar($I, $order->getId())['optimikWarnings'];

        $I->assertSame(['draft'], $warnings['unpublished']);
        $I->assertSame([['key' => 'serie-partial', 'quantity' => 7, 'serieSize' => 5]], $warnings['quantity']);
    }

    public function unknownObjectIsNotFound(FunctionalTester $I): void
    {
        $I->amLoggedInAsPimcoreAdmin();
        $I->amOnPage('/studio-ext/object/999999999/toolbar');

        $I->seeResponseCodeIs(404);
    }

    public function addChildrenCreatesObjectsOfTheParentClass(FunctionalTester $I): void
    {
        $parent = $I->haveDataObject('Product', key: 'SZAFA', published: false);

        $I->amLoggedInAsPimcoreAdmin();
        $I->sendJson('POST', "/studio-ext/object/{$parent->getId()}/children", ['keys' => ['SZAFA-80', 'SZAFA-100', 'SZAFA/120']]);

        $I->seeResponseCodeIs(201);
        $result = json_decode($I->grabResponseContent(), true);
        $I->assertSame(['SZAFA-80', 'SZAFA-100', 'SZAFA-120'], array_column($result['added'], 'key'), 'keys are made valid');

        foreach ($result['added'] as $added)
        {
            $child = DataObject::getById($added['id'], ['force' => true]);
            $I->assertInstanceOf(DataObject\Product::class, $child);
            $I->assertSame($parent->getId(), $child->getParentId());
            $I->assertFalse($child->isPublished());
        }
    }

    public function addChildrenSkipsExistingKeys(FunctionalTester $I): void
    {
        $parent = $I->haveDataObject('Product', key: 'REGAL', published: false);

        $I->amLoggedInAsPimcoreAdmin();
        $I->sendJson('POST', "/studio-ext/object/{$parent->getId()}/children", ['keys' => ['REGAL-1']]);
        $I->sendJson('POST', "/studio-ext/object/{$parent->getId()}/children", ['keys' => ['REGAL-1', 'REGAL-2']]);

        $result = json_decode($I->grabResponseContent(), true);
        $I->assertSame(['REGAL-2'], array_column($result['added'], 'key'));
        $I->assertSame([['key' => 'REGAL-1', 'error' => 'already exists']], $result['failed']);
    }

    public function addChildrenValidatesKeys(FunctionalTester $I): void
    {
        $parent = $I->haveDataObject('Product', published: false);
        $I->amLoggedInAsPimcoreAdmin();

        foreach ([[], ['ok', ''], [42], array_fill(0, 501, 'x')] as $keys)
        {
            $I->sendJson('POST', "/studio-ext/object/{$parent->getId()}/children", ['keys' => $keys]);
            $I->seeResponseCodeIs(400);
        }
    }

    public function addChildrenRequiresCreatePermission(FunctionalTester $I): void
    {
        $parent = $I->haveDataObject('Product', published: false);
        $I->amLoggedInAsPimcoreUser('test-viewer');

        $I->sendJson('POST', "/studio-ext/object/{$parent->getId()}/children", ['keys' => ['X-1']]);

        $I->seeResponseCodeIs(403);
    }

    private function toolbar(FunctionalTester $I, int $id): array
    {
        $I->amLoggedInAsPimcoreAdmin();
        $I->amOnPage("/studio-ext/object/$id/toolbar");
        $I->seeResponseCodeIs(200);

        return json_decode($I->grabResponseContent(), true);
    }

    private function markPublished(DataObject\Concrete ...$objects): void
    {
        foreach ($objects as $object)
        {
            \Pimcore\Db::get()->update('objects', ['published' => 1], ['id' => $object->getId()]);
            \Pimcore\Cache::clearTag('object_' . $object->getId());
            \Pimcore\Cache\RuntimeCache::clear();
        }
    }

    private function item(string $field, DataObject\Concrete $object, array $data): ObjectMetadata
    {
        $item = new ObjectMetadata($field, array_keys($data), $object);
        $item->setData($data);

        return $item;
    }
}
