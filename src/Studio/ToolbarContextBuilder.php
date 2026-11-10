<?php

declare(strict_types=1);

namespace App\Studio;

use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\User;
use Pimcore\Tool;

/**
 * Data the Megstyl Studio plugin (assets/studio) needs to render the editor toolbar of an object.
 *
 * Business rules live here (and in tests/Functional/Studio), the plugin only renders the result.
 */
class ToolbarContextBuilder
{
    public const EAN_MIN_LENGTH = 13;

    public function build(DataObject\Concrete $object, ?User $user): array
    {
        $language = $user?->getLanguage() ?: Tool::getDefaultLanguage() ?: 'pl';

        $context = [
            'id' => $object->getId(),
            'className' => $object->getClassName(),
            'key' => $object->getKey(),
            'published' => $object->isPublished(),
            'language' => $language,
            'websiteLanguages' => array_values(Tool::getValidLanguages()),
        ];

        // like the classic admin: inherited values, unpublished relations included
        $hideUnpublished = DataObject::doHideUnpublished();
        DataObject::setHideUnpublished(false);

        try
        {
            return DataObject\Service::useInheritedValues(true, fn () => $context + match (true) {
                $object instanceof DataObject\Product => $this->product($object, $language),
                $object instanceof DataObject\ProductSet => $this->productSet($object, $language),
                $object instanceof DataObject\Group => $this->group($object, $language),
                $object instanceof DataObject\User => $this->user($object),
                $object instanceof DataObject\Order => $this->order($object),
                default => [],
            });
        }
        finally
        {
            DataObject::setHideUnpublished($hideUnpublished);
        }
    }

    private function product(DataObject\Product $product, string $language): array
    {
        return [
            'quality' => $product->getObjectType() === 'ACTUAL' ? (int) $product->getQuality() : null,
            'hasEan' => strlen((string) $product->getEan()) >= self::EAN_MIN_LENGTH,
            'name' => $product->getName($language),
        ];
    }

    private function productSet(DataObject\ProductSet $set, string $language): array
    {
        return [
            'quality' => count($set->getSet()) > 0 ? (int) $set->getQuality() : null,
            'name' => $set->getName($language),
        ];
    }

    private function group(DataObject\Group $group, string $language): array
    {
        return [
            'name' => $group->getName($language),
            'description' => $group->getDescription($language),
            'productCount' => count($group->getProducts()) + count($group->getSets()),
        ];
    }

    private function user(DataObject\User $user): array
    {
        return [
            'offers' => array_map(static fn (DataObject\AbstractObject $offer) => $offer->getId(), $user->getOffers()),
        ];
    }

    /**
     * Checks before the Optimik export: unpublished products and quantities that do not fill whole series.
     */
    private function order(DataObject\Order $order): array
    {
        $unpublished = [];
        $quantity = [];

        foreach ($order->getProducts() as $item)
        {
            $product = $item instanceof ObjectMetadata ? $item->getObject() : $item;

            if (!$product instanceof DataObject\Concrete)
            {
                continue;
            }

            if (!$product->isPublished())
            {
                $unpublished[] = $product->getKey();
            }

            $ordered = $item instanceof ObjectMetadata ? (float) ($item->getData()['Quantity'] ?? 0) : 0;
            $serieSize = method_exists($product, 'getOptimikExportSerieSize') ? (float) $product->getOptimikExportSerieSize() : 0;

            if ($serieSize > 0 && fmod($ordered, $serieSize) != 0)
            {
                $quantity[] = ['key' => $product->getKey(), 'quantity' => $ordered, 'serieSize' => $serieSize];
            }
        }

        return ['optimikWarnings' => ['unpublished' => $unpublished, 'quantity' => $quantity]];
    }
}
