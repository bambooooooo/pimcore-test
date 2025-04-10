<?php

namespace App\Publishing;

use Pimcore\Model\DataObject\Product;

class ProductPublisher
{
    public function publish(Product $product): void
    {
        \Pimcore\Logger::info("Publishing product {$product->getId()}");
    }
}
