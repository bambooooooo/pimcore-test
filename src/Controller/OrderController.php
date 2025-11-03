<?php

namespace App\Controller;

use App\Service\OptimikService;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/orders')]
class OrderController extends FrontendController
{
    public function __construct(private readonly OptimikService $optimik)
    {

    }

    #[Route('/optimik/{id}', name: '_optimik')]
    public function optimikAction(Request $request): Response
    {
        $id = (int)$request->get('id');

        $obj = Dataobject::getByID($id);
        $orderCode = $obj->getKey();

        if($obj instanceof DataObject\Order)
        {
            $response = new StreamedResponse(function () use ($obj, $orderCode) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

                fprintf($handle, "[~(Z)~]\r\n");
                $dateAdd = date("Y-m-d");
                $dateDeadline = date("Y-m-d", strtotime($dateAdd . "+ 14 days"));
                $description = "";
                $externalNo = $obj->getId();

                fprintf($handle, ";{$dateAdd};{$orderCode};{$description};{$externalNo};{$dateDeadline};0;;1;+\r\n\r\n");
                fprintf($handle, "[~(X)~]\r\n");

                foreach ($obj->getProducts() as $lip)
                {
                    $row = $lip->getElement()->getKey() . ";" . $lip->getQuantity();
                    fprintf($handle, mb_convert_encoding($row, "UTF-8", "auto") . "\r\n");
                }

                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
            $response->headers->set('Content-Disposition', 'attachment; filename=' . $orderCode . '.csv');

            return $response;
        }

        return new Response("Object type is not supported", Response::HTTP_BAD_REQUEST);
    }

    #[Route('/sheets', name: '_sheets')]
    public function optimikGetBulkSheetsAction(Request $request): Response
    {
        $ids = $request->query->get('id');

        if(!$ids)
            return new Response("Empty id list", Response::HTTP_BAD_REQUEST);

        $data = $this->optimik->getBulkSheets($ids);

        return new JsonResponse($data);
    }
}
