<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Accessory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

#[Route('/accessory', name: 'accessory')]
class AccessoryController extends FrontendController
{
    public function __construct(private readonly CacheInterface $cache)
    {

    }

    #[Route('/{key}', name: '_by_key', methods: ['GET'])]
    public function getAccessoryIdFromKey(string $key)
    {
        return $this->cache->get('accessory_id_by_key_' . $key, function(ItemInterface $item) use($key) {
            DataObject::setHideUnpublished(false);

            $list = new Accessory\Listing();
            $list->setCondition("`key` = ?", [$key]);
            $list->load();

            $items = $list->getObjects();

            if($items)
            {
                $item->expiresAfter(30);
                return new Response($items[0]->getId(), Response::HTTP_OK);
            }

            $item->expiresAfter(60 * 60 * 24 * 7);
            return new Response("Not found", Response::HTTP_NOT_FOUND);
        });
    }
}
