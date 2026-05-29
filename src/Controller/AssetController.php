<?php

namespace App\Controller;

use CzProject\PdfRotate\PdfRotate;
use Imagick;
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

    #[Route('/rotate/{id}/{degrees}', name: 'rotate')]
    public function rotateLeftAction(Request $request)
    {
        $id = $request->get("id");
        $degrees = (int)$request->get("degrees");

        $possibleDegrees = [90, 180, 270];
        $pds = join(', ', $possibleDegrees);

        if(!in_array($degrees, $possibleDegrees)){
            return new Response("Invalid degree. Available degrees: {$pds}", Response::HTTP_BAD_REQUEST);
        }

        $asset = Asset::getById($id);

        if(!$asset) {
            return new Response("Asset not found", Response::HTTP_NOT_FOUND);
        }

        if ($asset->getMimeType() === 'application/pdf') {
            $pdf = new PdfRotate;
            $sourceFile = "../public/var/assets" . $asset->getPath() . $asset->getFilename();
            $outputFile = $sourceFile;

            $pdf->rotatePdf($sourceFile, $outputFile, $degrees);
            return new Response("Ok.", Response::HTTP_OK);
        }

	if($asset->getType() == 'image')
	{
		$im = Asset\Image::getById($id);
		$path = tempnam(sys_get_temp_dir(), "im_");

		$f = new Imagick($im->getFrontendPath());
		$f->rotateImage("#FFFFFF", $degrees);
		$f->writeImage($path);
		$im->setData(file_get_contents($path));
		$im->save();

		$f->clear();
		$f->destroy();

		return new Response("Ok. image", Response::HTTP_OK);
	}
        return new Response("Unsupported MIME type.", Response::HTTP_BAD_REQUEST);
    }
}
