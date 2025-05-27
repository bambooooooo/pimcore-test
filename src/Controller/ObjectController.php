<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Group;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Constraints\Json;
use ZipArchive;

class ObjectController extends FrontendController
{
    /**
     * Exports objects images
     *
     * @param Request $request
     * @return Response
     */
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

        $productImages = [];

        if($obj instanceof Product)
        {
            $productImages[$obj->getId()] = $this->getProductImages($obj);
        }
        elseif ($obj instanceof ProductSet)
        {
            $productImages[$obj->getId()] = $this->getProductSetImages($obj);
        }
        elseif ($obj instanceof Group)
        {
            if($obj->getImage())
            {
                $productImages[$obj->getKey()][] = $obj->getImage();
            }

            foreach ($obj->getProducts() as $product)
            {
                $productImages[$obj->getId()] = $this->getProductImages($product);
            }

            foreach ($obj->getSets() as $set)
            {
                $productImages[$set->getId()] = $this->getProductSetImages($set);
            }
        }
        else
        {
            return new Response("Unsupported object type [" . $obj->getType() . "]", Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        if(count($productImages) < 1)
        {
            return new Response('No images found', Response::HTTP_NOT_FOUND);
        }

        foreach ($productImages as $pid => $images)
        {
            $zip->addEmptyDir($pid);

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

                $zip->addFile($tmpFileName, $pid . "/" . $tmpFileName);
            }
        }

        $zip->close();

        $response = new BinaryFileResponse($tmpPath, 200, ['Content-Type' => 'application/zip'], true);
        $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $obj->getKey() . "-" . date("d-m-Y") . ".zip");

        foreach ($productImages as $pid => $images)
        {
            foreach ($images as $img)
            {
                $tmpFileName = $img->getFilename();
                unlink($tmpFileName);
            }
        }

        return $response;
    }

    private function getProductImages(Product $obj): array
    {
        $out = [];

        if($obj->getImage())
        {
            $out[] = $obj->getImage();
        }

        if($obj->getImages())
        {
            foreach($obj->getImages() as $image)
            {
                $out[] = $image->getImage();
            }
        }

        if($obj->getPhotos())
        {
            foreach($obj->getPhotos() as $image)
            {
                $out[] = $image->getImage();
            }
        }

        if($obj->getImagesModel())
        {
            foreach($obj->getImagesModel() as $image)
            {
                $out[] = $image->getImage();
            }
        }

        return $out;
    }

    private function getProductSetImages(ProductSet $obj): array
    {
        $out = [];

        if($obj->getImage())
        {
            $out[] = $obj->getImage();
        }

        if($obj->getImages())
        {
            foreach($obj->getImages() as $image)
            {
                $out[] = $image->getImage();
            }
        }

        return $out;
    }

    #[Route("/object/add-ean", name: "add_ean")]
    public function addEan(Request $request): Response
    {
        $id = $request->get("id");

        $obj = DataObject::getById($id);

        if($obj instanceof Product || $obj instanceof ProductSet) {
            if($obj->getEan() && $obj->getEan() != "" && strlen($obj->getEan()) > 12)
            {
                return new Response("Already added", Response::HTTP_OK);
            }

            if($obj instanceof Product && $obj->getObjectType() != 'ACTUAL')
            {
                return new Response("Cannot assign EAN to Product with type other than ACTUAL", Response::HTTP_CONFLICT);
            }

            $eanPools = new DataObject\EanPool\Listing();
            $eanPools->setCondition('LENGTH(`AvailableCodes`) > 12');
            $eanPools->load();

            if($eanPools->getCount() <= 0)
            {
                return new Response("No ean pool available. You have to extend pool collection. Maybe pool was not published?", Response::HTTP_CONFLICT);
            }

            if(!$obj->getName("pl"))
            {
                return new Response("Product has no PL name", Response::HTTP_NOT_FOUND);
            }

            $eanPool = $eanPools->current();

            $pool = $eanPool->getAvailableCodes();
            $code = array_shift($pool)['GTIN'];

            $cntRemain = count($pool);

            $obj->setEan($code);
            $eanPool->setAvailableCodes($pool);

            $obj->save();
            $eanPool->save();

            return new JsonResponse([
                'status' => 'success',
                'remaining' => $cntRemain
            ]);
        }

        return new Response("Object type not supported", Response::HTTP_NOT_IMPLEMENTED);
    }

    #[Route("/prices/{id}", name: "prices")]
    public function pricesAction(Request $request): Response
    {
        $id = $request->get("id");

        $obj = DataObject\Pricing::getById($id);

        $data = [
            'pricing' => $obj
        ];

        return $this->render('admin/prices.html.twig', $data);
    }
}
