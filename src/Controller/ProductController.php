<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products')]
class ProductController extends FrontendController
{
    public function __construct()
    {

    }

    #[Route('/baselinker/{id}', name: 'home')]
    public function defaultAction(Request $request): Response
    {
        $id = (int)$request->get('id');

        $obj = Dataobject::getByID($id);

        if(!$obj)
        {
            return new Response("Product or ProductSet not found", Response::HTTP_NOT_FOUND);
        }

        if($obj instanceof Product || $obj instanceof ProductSet)
        {
            $data = [];
            foreach ($obj->getBaselinkerCatalog() as $li)
            {
                /** @var DataObject\BaselinkerCatalog $catalog */
                $catalog = $li->getElement();

                $data[] = [
                    'catalogId' => $catalog->getCatalogId(),
                    'productId' => (int)$li->getProductId(),
                    'kind' => ($obj instanceof Product) ? "product" : "set",
                ];
            }

            return new JsonResponse($data);
        }

        return new Response("Object type is not supported", Response::HTTP_BAD_REQUEST);
    }
}
