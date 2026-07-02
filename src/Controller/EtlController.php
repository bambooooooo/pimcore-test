<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/etl', name: 'etl')]
class EtlController extends FrontendController
{
    #[Route('/packageserie', name: '_packagesserie')]
    public function packageCarrierAction(): Response
    {
        $response = new StreamedResponse(function ()
        {
            DataObject::setHideUnpublished(false);

            $packages = new DataObject\Package\Listing();
            $packages->setCondition("`ObjectType` = 'SKU'");
            $packages = $packages->load();

            $fp = fopen('php://output', 'w');

            foreach ($packages as $package) {
                $cnt = 0;

                if($package->getCarriers())
                {
                    foreach ($package->getCarriers() as $carrier)
                    {
                        $cnt = $carrier->getQuantity();
                        break;
                    }
                }

                fputcsv($fp, [
                    $package->getId(),
                    $cnt
                ]);
            }

            fclose($fp);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="packages.csv"');

        return $response;
    }

    #[Route('/field/{id}/{expression}', name: '_somefield')]
    public function getSomeField(Request $request): Response
    {
        $id = (int)$request->get('id');
        $expression = $request->get('expression');

        $obj = DataObject::getById($id);
        if(!$obj)
        {
            return new Response('Not found', 404);
        }

        try
        {
            if (preg_match('/^([a-zA-Z0-9_]+)\((.*)\)$/', $expression, $matches)) {
                $method = $matches[1];
                $argsRaw = trim($matches[2]);

                $args = [];

                if ($argsRaw !== '') {
                    $args = array_map(
                        static fn($arg) => trim($arg, " \t\n\r\0\x0B\"'"),
                        explode(',', $argsRaw)
                    );
                }

                if (!method_exists($obj, $method)) {
                    throw new \Exception("Method {$method} does not exist");
                }

                $data = $obj->$method(...$args);
            } else {
                $getter = 'get' . ucfirst($expression);

                if (!method_exists($obj, $getter)) {
                    throw new \Exception("Getter {$getter} does not exist");
                }

                $data = $obj->$getter();
            }

            return new Response($data ?? $expression, Response::HTTP_OK);
        }
        catch (\Exception $e)
        {
            return new Response($e->getMessage(), Response::HTTP_OK);
        }
    }
}
