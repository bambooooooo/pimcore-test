<?php

namespace App\Controller;

use App\Service\DeepLService;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition\Data\Input;
use Pimcore\Model\DataObject\Group;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use ZipArchive;

class ObjectController extends FrontendController
{

    public function __construct(private TranslatorInterface $translator,
                                private readonly DeepLService $deepLService)
    {

    }
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
        $mode = $request->get("mode") ?? "object";
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
            if($mode == "dependencies")
            {
                foreach($obj->getSet() as $lip)
                {
                    $productImages[$lip->getElement()->getId()] = $this->getProductImages($lip->getElement());
                }
            }
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

        if($obj->getInfographics())
        {
            foreach($obj->getInfographics() as $image)
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

        if($obj->getImagesModel())
        {
            foreach ($obj->getImagesModel() as $image)
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

    #[Route("/object/base-price", name: "add_baseprice")]
    public function basePriceAction(Request $request): Response
    {
        DataObject::setHideUnpublished(false);

        $id = $request->get("id");

        $obj = DataObject::getById($id);

        if($obj instanceof Product)
        {
            $baseprice = 0.0;

            if(!$obj->getPackages())
            {
                return new Response("No packages available.", Response::HTTP_NOT_FOUND);
            }

            foreach($obj->getPackages() as $lip)
            {
                if(!$lip->getElement()->getBasePrice())
                {
                    return new Response("No base price available for package [" . $lip->getElement()->getKey() . "]", Response::HTTP_NOT_FOUND);
                }

                if(!$lip->getQuantity())
                {
                    return new Response("No quantity for package [" . $lip->getElement()->getKey() . "]", Response::HTTP_NOT_FOUND);
                }

                $baseprice += $lip->getElement()->getBasePrice()->getValue() * $lip->getQuantity();
            }

            $PLN = DataObject\QuantityValue\Unit::getById("PLN");
            $bp  = new DataObject\Data\QuantityValue($baseprice, $PLN);
            $obj->setBasePrice($bp);
            $obj->save();

            return new JsonResponse(["status" => "success"]);
        }

        return new Response("Object type not supported", Response::HTTP_NOT_IMPLEMENTED);
    }

    #[Route("/prices/{id}", name: "prices")]
    public function pricesAction(Request $request): Response
    {
        $id = $request->get("id");
        $kind = $request->get("kind") ?? "preview";
        $references = $request->get("references") ?? [];
        $filename = $request->get("filename") ?? null;

        $obj = DataObject\Offer::getById($id);

        if($kind == "xlsx")
        {
            return $this->offerPriceListXlsx($obj, $references, $filename);
        }

        $data = [
            'pricing' => $obj,
            'references' => $references,
            'show_indices' => $request->get("show_indices") ?? false
        ];

        return $this->render('admin/prices.html.twig', $data);
    }

    private function offerPriceListXlsx(Offer $offer, array $references = [], string $filename = null): Response
    {
        DataObject::setHideUnpublished(false);
        $id = $offer->getId();

        $spreadsheet = new SpreadSheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle($offer->getKey());
        $sheet->setCellValue('A1', '#');
        $sheet->setCellValue('B1', $this->translator->trans('Image'));
        $sheet->setCellValue('C1', $this->translator->trans('Sku'));
        $sheet->setCellValue('D1', $this->translator->trans('Ean'));
        $sheet->setCellValue('E1', $this->translator->trans('Name'));
        $sheet->setCellValue('F1', $this->translator->trans('Width'));
        $sheet->setCellValue('G1', $this->translator->trans('Height'));
        $sheet->setCellValue('H1', $this->translator->trans('Depth'));
        $sheet->setCellValue('I1', $offer->getName());

        $i = 10;
        foreach($references as $reference)
        {
            $refOffer = DataObject\Offer::getById($reference);
            $sheet->setCellValue([$i, 1], $refOffer->getName());
            $i++;
        }

        $sheet->getStyle('E')->getAlignment()->setWrapText(true);

        $i = 2;
        foreach ($offer->getDependencies()->getRequiredBy() as $req)
        {
            $obj = DataObject::getById($req['id']);

            if(!$obj)
                continue;

            if(!($obj instanceof Product || $obj instanceof ProductSet))
                continue;

            $sheet->getRowDimension($i)->setRowHeight(64);
            $sheet->setCellValue('A' . $i, $i - 1);
            $sheet->setCellValue('C' . $i, $obj->getId());
            $sheet->setCellValueExplicit('D' . $i, $obj->getEan(), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $i, $obj->getName());

            if($obj instanceof Product)
            {
                $sheet->setCellValue('F' . $i, $obj->getWidth());
                $sheet->setCellValue('G' . $i, $obj->getHeight());
                $sheet->setCellValue('H' . $i, $obj->getDepth());
            }

            foreach ($obj->getPrice() as $price)
            {
                if($price->getElement()->getId() == $offer->getId())
                {
                    $price = round(floatval($price->getPrice()), 2);
                    $sheet->setCellValue('I' . $i, $price);
                }
            }

            $j = 10;
            foreach($references as $reference)
            {
                foreach ($obj->getPrice() as $price)
                {
                    if ($price->getElement()->getId() == $reference) {
                        $price = round(floatval($price->getPrice()), 2);
                        $sheet->setCellValue([$j, $i], $price);
                    }
                }

                $j++;
            }

            if ($obj->getImage()) {

                $image = $obj->getImage()->getThumbnail("200x200");

                $stream = $image->getStream();

                // Create temporary file
                $tempFile = tempnam(sys_get_temp_dir(), 'pim_image_');
                file_put_contents($tempFile, stream_get_contents($stream));

                if (file_exists($tempFile)) {
                    $drawing = new Drawing();
                    $drawing->setPath($tempFile);
                    $drawing->setHeight(80); // Set image height (adjust as needed)
                    $drawing->setCoordinates('B' . $i); // Place image in column D
                    $drawing->setWorksheet($sheet);
                }
            }

            $i++;
        }

        $columns = 9 + count($references);

        for ($j=0; $j<$columns; $j++)
        {
            if($j == 1)
            {
                $sheet->getColumnDimension(chr(833 + $j))->setWidth(12);
            }
            else
            {
                $sheet->getColumnDimension(chr(833 + $j))->setAutoSize(true);
            }
        }

        $writer = new Xlsx($spreadsheet);

        if(!$filename)
        {
            $fileName = $offer->getKey() . '.xlsx';
        }
        else
        {
            $fileName = $filename . '.xlsx';
        }

        $response = new Response();
        $response->headers->set('Content-Type', 'application/vnd.ms-excel');
        $response->headers->set('Content-Disposition', 'attachment;filename="' . $fileName . '"');

        ob_start();
        $writer->save('php://output');
        $response->setContent(ob_get_clean());

        return $response;
    }

    #[Route("/object/translate-name", name: "translate_name")]
    public function translateAction(Request $request): Response
    {
        DataObject::setHideUnpublished(false);

        $obj = DataObject::getById($request->get("id"));

        $text = $request->get("name");
        $origin = $request->get("origin");
        $locale = $request->get("loc");
        $deeplLocale = ($locale == "en") ? "EN-US" : $locale;
        $field = $request->get("field");

        if(!$obj)
        {
            return new Response("Object not found", Response::HTTP_NOT_FOUND);
        }

        if($field == "name" && ($obj instanceof Product or $obj instanceof ProductSet or $obj instanceof Group))
        {
            $class = DataObject\ClassDefinition::getByName($obj->getClassName());

            /** @var Input $nameDefinition */
            $nameDefinition = $class->getFieldDefinition('Name');
            $w = $nameDefinition->getColumnLength();

            $tx = $this->deepLService->translate($text, $deeplLocale, $origin);

            $trimmed = $tx;

            while(strlen($trimmed) > 0)
            {
                if(strlen($trimmed) <= $w)
                    break;

                $trimmed = $this->removeLastWord($trimmed);
            }

            $obj->setName($trimmed, $locale);
            $obj->save();

            return new JsonResponse(["status" => $obj->getKey() . "[" . $locale . "] = " . $trimmed]);
        }

        if($field == "description" && $obj instanceof Group)
        {
            $tx = $this->deepLService->translate($text, $deeplLocale, $origin);

            $obj->setDescription($tx, $locale);
            $obj->save();

            return new JsonResponse(["status" => $obj->getKey() . "[" . $locale . "] = " . $tx]);
        }

        throw new \Exception("Object [class: " . $obj->getClassId() . "] or field: " . $field . " not supported", Response::HTTP_BAD_REQUEST);
    }

    private function removeLastWord(string $input): string
    {
        $input = trim($input);
        $lastSpacePos = strrpos($input, ' ');

        if($lastSpacePos === false)
        {
            return '';
        }
        return substr($input, 0, $lastSpacePos);
    }
}
