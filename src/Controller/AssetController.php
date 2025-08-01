<?php

namespace App\Controller;

use CzProject\PdfRotate\PdfRotate;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\Asset;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/assets', name: 'assets_')]
class AssetController extends FrontendController
{
    public function __construct()
    {

    }

    #[Route('/rotate/{id}', name: 'rotate')]
    public function rotateLeftAction(Request $request)
    {
        $id = $request->get("id");

        $asset = Asset::getById($id);

        if(!$asset) {
            return new Response("Asset not found", Response::HTTP_NOT_FOUND);
        }

        if ($asset->getMimeType() === 'application/pdf') {
            $pdf = new PdfRotate;
            $sourceFile = "../public/var/assets" . $asset->getPath() . $asset->getFilename();
            $outputFile = $sourceFile;

            $pdf->rotatePdf($sourceFile, $outputFile, $pdf::DEGREES_270);
            return new Response("Ok.", Response::HTTP_OK);
        }

        return new Response("Unsupported MIME type.", Response::HTTP_BAD_REQUEST);
    }
}
