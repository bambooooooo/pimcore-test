<?php

namespace App\Controller;

use App\Message\FeedMessage;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
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
}
