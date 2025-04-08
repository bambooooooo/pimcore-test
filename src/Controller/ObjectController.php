<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use ZipArchive;

class ObjectController extends FrontendController
{
    #[Route("/export/images/{id}", name: "export_images")]
    public function exportImagesAction(Request $request): Response
    {
        $id = $request->get("id");
        $obj = DataObject::getById($id);

        if(!$obj) {
            return new Response("Not found", 404);
        }

        $tmpPath = sys_get_temp_dir() . '/' . $obj->getKey() . '.zip';

        $zip = new ZipArchive();

        if($zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true)
        {
            return new Response('Could not create ZIP file', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $images = [];

        if($obj instanceof Product || $obj instanceof ProductSet) {
            if($obj->getImage())
            {
                $images[] = $obj->getImage();
            }

            if($obj->getImages())
            {
                foreach($obj->getImages() as $image)
                {
                    $images[] = $image->getImage();
                }
            }
        }
        else
        {
            return new Response("Unsupported object type [" . $obj->getType() . "]", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if(count($images) < 1)
        {
            return new Response('No images found', Response::HTTP_NOT_FOUND);
        }

        foreach ($images as $img)
        {
            $url = $request->getSchemeAndHttpHost() . $img;

            if($request->getSchemeAndHttpHost() == "http://localhost")
            {
                $url = "http://10.10.1.1" . $img;
            }

            $tmpFileName = $img->getFilename();
            $fileContent = file_get_contents($url);

            file_put_contents($tmpFileName, $fileContent);

            $zip->addFile($tmpFileName);
        }

        $zip->close();

        $response = new BinaryFileResponse($tmpPath, 200, ['Content-Type' => 'application/zip'], true);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $obj->getKey() . "-" . date("d-m-Y") . ".zip");

        foreach ($images as $img)
        {
            $tmpFileName = $img->getFilename();
            unlink($tmpFileName);
        }

        return $response;
    }
}
