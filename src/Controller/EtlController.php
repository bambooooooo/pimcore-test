<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
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
}
