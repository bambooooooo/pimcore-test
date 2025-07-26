<?php

namespace App\Publishing;

use App\Service\BrokerService;
use Pimcore\Model\DataObject\Order;

class OrderPublisher
{
    public function __construct(private BrokerService $rabbit)
    {

    }
    public function publish(Order $order): void
    {
        $this->assertProductArePublished($order);

        if($order->getUser())
        {
//            TURN ON WHEN SGT BRIDGE ORDER API WILL BE AVAILABLE
//            $this->sendToERP($order);
        }
    }

    private function assertProductArePublished(Order $order): void
    {
        if($order->getUser())
        {
            $name = $order->getUser()->getKey();
            assert($order->getUser()->isPublished(), "Order.User [$name] must be published");

            if(!$order->hasChildren())
            {
                assert($order->getProducts(), "Order with specified user has to get products");
            }
        }

        foreach ($order->getProducts() as $li) {
            assert($li->getElement()->isPublished(), "Product [" . $li->getElement()->getKey() . "] must be published");
            assert($li->getQuantity() > 0, "Product [" . $li->getElement()->getKey() . "] quantity must be greater than zero");
        }
    }
}
