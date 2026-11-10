<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Asset;
use Pimcore\Model\DataObject;
use Pimcore\Model\Element\ElementInterface;

/**
 * Picks the AdminStyle for an element (tree icon and tooltip in Pimcore Studio).
 */
class AdminStyleResolver
{
    public function resolve(ElementInterface $element): ?AdminStyle
    {
        return match (true) {
            $element instanceof DataObject\Product => new Product($element),
            $element instanceof DataObject\ProductSet => new ProductSet($element),
            $element instanceof DataObject\Group => new Group($element),
            $element instanceof DataObject\EanPool => new EanPool($element),
            $element instanceof DataObject\User => new User($element),
            $element instanceof DataObject\Package => new Package($element),
            $element instanceof DataObject\LoadCarrier => new LoadCarrier($element),
            $element instanceof DataObject\Order => new Order($element),
            $element instanceof Asset\Image => new Image($element),
            $element instanceof Asset\Document => new Document($element),
            $element instanceof Asset\Folder => new AssetFolder($element),
            default => null,
        };
    }
}
