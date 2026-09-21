<?php

namespace App\Controller;

use App\Message\FeedMessage;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Objectbrick\Data\SelectedGroups;
use Pimcore\Model\DataObject\Offer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/offer', name: 'feed')]
class OfferController extends FrontendController
{
    public function __construct(private readonly MessageBusInterface $messageBus)
    {

    }

    #[Route('/feed/{id}', name: '_generate', methods: ['GET'])]
    public function generateTriggerAction(int $id): Response
    {
        $offer = DataObject\Offer::getById($id);
        if(!$offer) {
            return new Response("Offer $id not found", Response::HTTP_NOT_FOUND);
        }

        $this->messageBus->dispatch(new FeedMessage($offer->getId()));

        return new Response("Ok", Response::HTTP_OK);
    }

    #[Route('/refresh-products/{id}', name: '_refresh_products', methods: ['PATCH'])]
    public function refreshProducts(Request $request): Response
    {
        DataObject::setHideUnpublished(false);

        $id = $request->get('id');
        $offer = DataObject\Offer::getById($id);

        if(!$offer) {
            return new Response("Offer $id not found", Response::HTTP_NOT_FOUND);
        }

        if(!$offer->getPrice())
        {
            return new Response("No price selected for offer $id", Response::HTTP_NOT_FOUND);
        }

        $price = $offer->getPrice();

        $listing = new DataObject\Product\Listing();

        $condition = $price . '__value > 0 AND ObjectType IN (:objectType) AND Status IN (:status)';
        $params = [
            'objectType' => ["ACTUAL", "SKU"],
            'status' => ["ACTIVE", "SALE"]
        ];

        $batches = $offer->getFilters()->getItems();

        foreach ($batches as $batch)
        {
            if($batch instanceof SelectedGroups)
            {
                $conditions = [];
                foreach($batch->getGroups() as $group)
                {
                    $parameter = "group" . $group->getId();

                    $conditions[] = "Groups LIKE :{$parameter}";
                    $params[$parameter] = '%,' . $group->getId() .',%';
                }

                $condition .= " AND " . '(' . implode(" OR ", $conditions) . ')';
            }
        }

        $listing->setCondition($condition, $params);
        $listing->setOrderKey('key');
        $listing->setOrder('asc');

        $products = $listing->load();

        $json = json_encode($batches);

        $offer->setProducts($products);
        $offer->save();

        return new Response($json, Response::HTTP_OK);
    }
}
