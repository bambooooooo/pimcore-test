<?php

namespace App\Controller;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
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
use Symfony\Contracts\Translation\TranslatorInterface;

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

	    // if 7539 (KN) then add 7495 (A1) x1
	    // always.
	    if($id == 7539) {
		$items[] = [
			"id" => 7495,
			"count" => 1
		];
	    }
	}

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

    #[Route("/fillments/{id}", name: '_fillments', methods: ['GET'])]
    public function productPackagesFillmentAction(Request $request): Response
    {
        $id = (int)$request->get('id');

        if(!Product::getByID($id))
        {
            return new Response("Product not found", Response::HTTP_NOT_FOUND);
        }

        $product = Product::getByID($id);

        $data = [];

        foreach($product->getPackages() as $lip)
        {
            /** @var DataObject\Package $p */
            $p = $lip->getElement();

            foreach($p->getFillment() as $fillmentGroup)
            {
                $material = $fillmentGroup["Material"]->getData();
                preg_match("/Plaster miodu - oczko (\d+) mm/i", $material, $matches);
                $type = isset($matches[1]) ? (int)$matches[1] : null;

                $thickness = (int)$fillmentGroup["Thickness"]->getData()->getValue();

                foreach($fillmentGroup["Elements"]->getData() as $fill)
                {
                    $data[] = [
                        'packageId' => $p->getId(),
                        'packageSku' => $p->getKey(),
                        'material' => $material,
                        'length' => (int)$fill[0],
                        'width' => (int)$fill[1],
                        'thickness' => (int)$thickness,
                        'type' => $type,
                        'count' => (int)$fill[2] * (int)$lip->getQuantity(),
                    ];
                }

            }
        }

        usort($data, function($a, $b) {
            return ($a['type'] <=> $b['type'])
                ?: ($a['thickness'] <=> $b['thickness'])
                ?: ($a['length'] <=> $b['length'])
                ?: ($a['width'] <=> $b['width']);
        });

        $spreadsheet = new SpreadSheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle($product->getKey());
        $sheet->setCellValue('A1', '#');
        $sheet->setCellValue('B1', 'Materiał');
        $sheet->setCellValue('C1', 'Długość');
        $sheet->setCellValue('D1', 'Szerokość');
        $sheet->setCellValue('E1', 'Grubość');
        $sheet->setCellValue('F1', 'Rozmiar oczka');
        $sheet->setCellValue('G1', 'Ilość');

        $i = 2;
        foreach($data as $row)
        {
            $sheet->setCellValue("A$i", $i - 1);
            $sheet->setCellValue("B$i", $row['material']);
            $sheet->setCellValue("C$i", $row['length']);
            $sheet->setCellValue("D$i", $row['width']);
            $sheet->setCellValue("E$i", $row['thickness']);
            $sheet->setCellValue("F$i", $row['type']);
            $sheet->setCellValue("G$i", $row['count']);

            $i++;
        }

        for ($j=0; $j<8; $j++)
        {
            $sheet->getColumnDimension(chr(833 + $j))->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);

        $fileName = $product->getKey() . '.xlsx';

        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');

        ob_start();
        $writer->save('php://output');
        $response->setContent(ob_get_clean());

        return $response;
    }
}
