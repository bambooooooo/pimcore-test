<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Accessory;
use Pimcore\Model\DataObject\Data\BlockElement;
use Pimcore\Model\DataObject\Data\ObjectMetadata;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/products', name: 'products')]
class ProductController extends FrontendController
{
    public function __construct()
    {

    }

    #[Route('/baselinker/{id}', name: '_baselinkerid')]
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

    #[Route('/accessory/{id}', name: '_update_accessory', methods: ['POST'])]
    public function updateProductAccessoryAction(Request $request): Response
    {
        DataObject::setHideUnpublished(false);
        $id = (int)$request->get('id');
        $items = json_decode($request->getContent(), true)['items'];

        if(!Product::getByID($id))
        {
            return new Response("Product not found", Response::HTTP_NOT_FOUND);
        }

        $product = Product::getByID($id);
        $relations = [];

        foreach($items as $item)
        {
            $id = $item['id'];
            $count = $item['count'];

            if(!Accessory::getByID($id))
            {
                return new Response("Accessory with id={$id} not found", Response::HTTP_NOT_FOUND);
            }

	    $acc = Accessory::getById($id);

	    $g = $acc->getSupplier()?->getId() ?? 0;
	    $g = $g . "-" . $acc->getSetContent() ?? 0;

            $relation = new ObjectMetadata('AccessorySets', ['Quantity'], DataObject::getById($id));
            $relation->setQuantity((int)$count);
            $relations[$g][] = $relation;
        }

        $accSets = [];

        foreach($relations as $supplierId => $supplierRelations)
        {
            $data = [
                "Content" => new BlockElement('Content', 'select', null),
                "Length" => new BlockElement('Length', 'quantityvalue', new DataObject\Data\QuantityValue(null, DataObject\QuantityValue\Unit::getById('mm'))),
                "Width" => new BlockElement('Width', 'quantityvalue', new DataObject\Data\QuantityValue(null, DataObject\QuantityValue\Unit::getById('mm'))),
                "Height" => new BlockElement('Height', 'quantityvalue', new DataObject\Data\QuantityValue(null, DataObject\QuantityValue\Unit::getById('mm'))),
                "Set" => new BlockElement('Set', 'advancedManyToManyRelation', $supplierRelations),
            ];

            $accSets[] = $data;
        }

        $product->setAccessorySets($accSets);
        $product->save(["skip" => "Update accessory list"]);

        return new Response("Ok", Response::HTTP_OK);
    }
}
