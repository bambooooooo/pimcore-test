<?php

namespace App\Controller;

use App\Service\DeepLService;
use Carbon\Carbon;
use DeepL\DeepLException;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\ClassDefinition\Data\Input;
use Pimcore\Model\DataObject\Group;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use ZipArchive;

class ObjectController extends FrontendController
{

    public function __construct(private TranslatorInterface $translator,
                                private readonly DeepLService $deepLService,
                                private readonly CacheItemPoolInterface $cache)
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
        DataObject::setHideUnpublished(false);

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
                if($product->getObjectType() != 'ACTUAL')
                {
                    continue;
                }

                $itemImages = $this->getProductImages($product);
                if(count($itemImages) <= 0)
                {
                    continue;
                }

                $k = $this->sanitizeToFilename($product->getKey()) . " - " . $product->getId();
                $productImages[$k] = $itemImages;
            }

            foreach ($obj->getSets() as $set)
            {
                $itemImages = $this->getProductSetImages($set);

                if(count($itemImages) <= 0)
                    continue;

                $k = $this->sanitizeToFilename($set->getKey()) . " - " . $set->getId();
                $productImages[$k] = $itemImages;
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

    #[Route('/object/{_locale}/{id}/datasheet', name: 'datasheet_new', defaults: ['_locale' => 'pl', 'locale' => 'pl'])]
    public function datasheetAction(Request $request): Response
    {
        DataObject::setHideUnpublished(false);
        $obj = DataObject::getById($request->get('id'));
        if(!$obj)
            return new Response("Not found", Response::HTTP_NOT_FOUND);

        $orderBy = $request->get('orderby') ?? 'sku';

        $params = [
            'paperWidth' => '210mm',
            'paperHeight' => '297mm',
            'marginTop' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0,
            'marginRight' => 0,
            "displayHeaderFooter" => true,
            'metadata' => [
                'Title' => $obj->getKey(),
                'Author' => 'pim'
            ]
        ];

        $adapter = \Pimcore\Bundle\WebToPrintBundle\Processor::getInstance();

        $html = "";

        if($obj instanceof DataObject\Product)
        {
            $html = $this->renderView('factory/pdf/datasheet_product.html.twig', ['obj' => $obj]);
        }
        elseif ($obj instanceof DataObject\Group)
        {
            $productListing = new DataObject\Product\Listing();
            $productListing->setCondition("Groups like '%," . $obj->getId() . ",%' AND `ObjectType`='ACTUAL' ");
            $prods = $productListing->load();

            usort($prods, function (DataObject\Product $a, DataObject\Product $b) use ($orderBy) {

                if($orderBy == 'name')
                {
                    return strcmp($a->getName() ?? $a->getKey(), $b->getName() ?? $b->getKey());
                }

                return strcmp($a->getKey(), $b->getKey());
            });

            $setListing = new DataObject\ProductSet\Listing();
            $setListing->setCondition("Groups like '%," . $obj->getId() . ",%' ");
            $sets = $setListing->load();

            usort($sets, function ($a, $b) {
                return $a->getBasePrice()->getValue() > $b->getBasePrice()->getValue();
            });

            $common = [];

            foreach ($prods as $prod)
            {
                $common[] = $prod;
            }

            foreach($sets as $set)
            {
                foreach($set->getSet() as $lip)
                {
                    $product = $lip->getElement();
                    if($product)
                    {
                        $common[] = $product;
                    }
                }
            }

            $common = array_unique($common);

            usort($common, function (DataObject\Product $a, DataObject\Product $b) use ($orderBy) {

                if($orderBy == 'group-name')
                {
                    if($a->getGroup() != null && $b->getGroup() != null)
                    {
                        $comp = strcmp($a->getGroup()->getName() ??  $a->getGroup()->getKey(), $b->getGroup()->getName() ?? $b->getGroup()->getKey());

                        if($comp === 0)
                        {
                            return strcmp($a->getName() ?? $a->getKey(), $b->getName() ?? $b->getKey());
                        }
                    }
                }
                else if ($orderBy == 'name')
                {
                    return strcmp($a->getName() ?? $a->getKey(), $b->getName() ?? $b->getKey());
                }

                return strcmp($a->getKey(), $b->getKey());
            });

            if($request->get("type") == 'xlsx')
            {
                $offer = DataObject\Offer::getById($request->get("show_prices"));

                if(!$offer)
                {
                    return new Response("No Offer found", Response::HTTP_BAD_REQUEST);
                }

                $data = $prods;
                $fname = strtoupper(implode('-', [
                    $obj->getName() ?? $obj->getKey(),
                    $this->translator->trans("Pricelist"),
                    $offer->getName() ?? $offer->getKey(),
                    date("d-m-Y")
                ])) . ".xlsx";

                return $this->getSheetPricesXlsx($data, $offer, $fname);
            }

            $offer = DataObject\Offer::getById($request->get("show_prices"));

            if($offer)
            {
                $fname = strtoupper(implode('-', [
                    $obj->getName() ?? $obj->getKey(),
                    $this->translator->trans("Pricelist"),
                    $offer->getName() ?? $offer->getKey(),
                    date("d-m-Y"),
                ])) . ".pdf";
            }
            else
            {
                $fname = implode('-', [
                    $obj->getName() ?? $obj->getKey(),
                    $this->translator->trans("Datasheet"),
                    date("d-m-Y"),
                    ".pdf"
                ]);
            }

            $params['metadata']['Title'] = $fname;

            $html = $this->renderView('factory/pdf/datasheet_group.html.twig', [
                'group' => $obj,
                'prods' => $prods,
                'sets' => $sets,
                'common' => $common,
                'sets_row_cnt' => $request->query->get("sets") ?? 5,
                'products_row_cnt' => $request->query->get("products") ?? 5,
                'new_after_date' => (new Carbon("now"))->subDays((int)$request->query->get("new") ?? 1),
                'prices' => $request->get("show_prices"),
            ]);
        }
        elseif($obj instanceof DataObject\Offer)
        {
            $prods = [];
            $sets = [];
            $common = [];

            foreach ($obj->getDependencies()->getRequiredBy() as $dependency)
            {
                $item = DataObject::getById($dependency['id']);
                if($item instanceof DataObject\Product)
                {
                    $prods[] = $item;
                    $common[] = $item;
                }
                elseif($item instanceof DataObject\ProductSet)
                {
                    $sets[] = $item;

                    foreach($item->getSet() as $lip)
                    {
                        $product = $lip->getElement();
                        if($product)
                        {
                            $common[] = $product;
                        }
                    }
                }
            }

            $common = array_unique($common);

            $html = $this->renderView('factory/pdf/datasheet_group.html.twig', [
                'group' => $obj,
                'prods' => $prods,
                'sets' => $sets,
                'common' => $common,
                'sets_row_cnt' => $request->query->get("sets") ?? 5,
                'products_row_cnt' => $request->query->get("products") ?? 5,
                'new_after_date' => (new Carbon("now"))->subDays((int)$request->query->get("new") ?? 1),
                'prices' => $request->get("show_prices"),
            ]);
        }

        $pdf = $adapter->getPdfFromString($html, $params);

//        return new Response($html, 200);
        return new Response($pdf, Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
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

        $productSheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle($this->translator->trans("Products"));
        $sheet->setCellValue('A1', '#');
        $sheet->setCellValue('B1', $this->translator->trans('Image'));
        $sheet->setCellValue('C1', $this->translator->trans('Sku'));
        $sheet->setCellValue('D1', $this->translator->trans('Ean'));
        $sheet->setCellValue('E1', $this->translator->trans('Name'));
        $sheet->setCellValue('F1', $this->translator->trans('Width'));
        $sheet->setCellValue('G1', $this->translator->trans('Height'));
        $sheet->setCellValue('H1', $this->translator->trans('Depth'));
        $sheet->setCellValue('I1', $offer->getName());

        $ws = new Worksheet($sheet->getParent());
        $sheetSets = $spreadsheet->addSheet($ws);
        $sheetSets->setTitle($this->translator->trans("Sets"));
        $sheetSets->setCellValue('B1', $this->translator->trans('Image'));
        $sheetSets->setCellValue('C1', $this->translator->trans('Sku'));
        $sheetSets->setCellValue('D1', $this->translator->trans('Ean'));
        $sheetSets->setCellValue('E1', $this->translator->trans('Name'));
        $sheetSets->setCellValue('F1', $this->translator->trans('Width'));
        $sheetSets->setCellValue('G1', $this->translator->trans('Height'));
        $sheetSets->setCellValue('H1', $this->translator->trans('Depth'));
        $sheetSets->setCellValue('I1', $offer->getName());

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

            if(!($obj instanceof Product))
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

        $sheet = $ws;
        $i = 2;
        foreach ($offer->getDependencies()->getRequiredBy() as $req)
        {
            $obj = DataObject::getById($req['id']);

            if(!$obj)
                continue;

            if(!($obj instanceof ProductSet))
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
                $productSheet->getColumnDimension(chr(833 + $j))->setWidth(12);
                $sheetSets->getColumnDimension(chr(833 + $j))->setWidth(12);
            }
            else
            {
                $productSheet->getColumnDimension(chr(833 + $j))->setAutoSize(true);
                $sheetSets->getColumnDimension(chr(833 + $j))->setAutoSize(true);
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

            try
            {
                $tx = $this->deepLService->translate($text, $deeplLocale, $origin);
            }
            catch (DeepLException $e)
            {
                return new Response($e->getMessage(), Response::HTTP_TOO_MANY_REQUESTS);
            }

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

    #[Route("/objects/mainimage/{id}", name: "main_image")]
    public function getLastImageAction(Request $request, int $id): Response
    {
        $obj = DataObject::getById($id);
        if(!$obj)
        {
            return new Response("Object not found", Response::HTTP_NOT_FOUND);
        }

        if($obj instanceof Product or $obj instanceof ProductSet)
        {
            if($obj->getImage())
            {
                $thumbnail = $obj->getImage()->getThumbnail('200x200');

                $response = new StreamedResponse(function () use ($thumbnail) {
                    fpassthru($thumbnail->getStream());
                });

                $response->headers->set('Content-Type', $obj->getImage()->getMimeType());
                $response->headers->set('Content-Disposition', 'inline; filename="' . $obj->getImage()->getFilename() . '"');

                return $response;
            }
        }

        return new Response("Object type not supported", Response::HTTP_BAD_REQUEST);
    }

    #[Route("/objects/stocks/{id}/{stocks}", name: "stock_update")]
    public function updateStocksAction(Request $request, int $id, int $stocks): Response
    {
        $obj = DataObject::getById($id);
        if(!($obj instanceof Product || $obj instanceof ProductSet)){
            return new Response("Object not found", Response::HTTP_BAD_REQUEST);
        }

        if($stocks < 0)
            return new Response("Stocks must be greater than 0", Response::HTTP_BAD_REQUEST);

        if($stocks != $obj->getStock())
        {
            $obj->setStock($request->get("stocks"));
        }

        return new Response("Ok", Response::HTTP_OK);
    }

    #[Route("/objects/stocks", name: "stock_bulk_update", methods: ["POST"])]
    public function updateBulkStocksAction(Request $request): Response
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $found = 0;
        $changed = 0;
        $skipped = 0;

        /** @var int $stock */
        /** @var int $id */
        foreach($data as $id => $stock)
        {
            $obj = DataObject::getById($id);
            if(!($obj instanceof Product || $obj instanceof ProductSet))
            {
                continue;
            }

            $found++;

            if($obj->getStock() != $stock)
            {
                $obj->setStock($stock);
                $changed++;
            }
            else
            {
                $skipped++;
            }
        }

        return new Response("Ok. Found: {$found}, Changed: {$changed}, Skipped: {$skipped}", Response::HTTP_OK);
    }

    #[Route("objects/status/{id}", name: "objects_status")]
    public function getStatusAction(int $id): Response
    {
        $obj = DataObject::getById($id);

        if(!$obj)
        {
            return new Response("Object not found", Response::HTTP_BAD_REQUEST);
        }

        $key = "object_status_{$id}";

        $item = $this->cache->getItem($key);
        $data = $item->isHit() ? $item->get() : "";

        return new Response($data, Response::HTTP_OK);
    }

    #[Route("/offers", name: "get_offers")]
    public function getOffersHead(): JsonResponse
    {
        $offers = new Offer\Listing();
        $offers->setUnpublished(false);
        $ret = [
            'data' => []
        ];
        foreach($offers as $offer)
        {
            $ret['data'][] = [
                'id' => $offer->getId(),
                'name' => $offer->getKey(),
            ];
        }

        return new JsonResponse($ret, Response::HTTP_OK);
    }

    private function getSheetPricesXlsx(array $items, Dataobject\Offer $offer, string $filename = null): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($this->translator->trans('Price list'));

        $sheet->setCellValue("A1", "#");
        $sheet->setCellValue("B1", $this->translator->trans("Image"));
        $sheet->setCellValue("C1", $this->translator->trans("Product"));
        $sheet->setCellValue("D1", $this->translator->trans("Name"));
        $sheet->setCellValue("E1", $this->translator->trans("Description"));
        $sheet->setCellValue("F1", $this->translator->trans("Net price"));

        $i = 2;

        /** @var Product|ProductSet $obj */
        foreach ($items as $obj)
        {
            $price = null;
            foreach ($obj->getPrice() as $price)
            {
                if($price->getElement()->getId() == $offer->getId())
                {
                    $price = round(floatval($price->getPrice()), 2);
                    break;
                }
            }

            if(!$price)
                continue;

            $sheet->setCellValue('F' . $i, $price);

            $sheet->setCellValue('A' . $i, $i - 1);

            if(!$obj->getSummary() || (substr_count($obj->getSummary(), "</p>") < 5))
            {
                $sheet->getRowDimension($i)->setRowHeight(64);
            }

            if ($obj->getImage()) {

                $image = $obj->getImage()->getThumbnail("200x200");

                $stream = $image->getStream();

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

            $sheet->setCellValue('C' . $i, $obj->getKey());
            $sheet->setCellValue('D' . $i, $obj->getName());

            if($obj->getSummary())
            {
                $summary = new \PhpOffice\PhpSpreadsheet\Helper\Html();
                $html = $summary->toRichTextObject($obj->getSummary());
                $sheet->setCellValue('E' . $i, $html);
            }

            $i++;
        }

        for ($j=0; $j<6; $j++)
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

        $sheet->getStyle("B1:B" . $sheet->getHighestRow())->getAlignment()->setWrapText(true);
        $sheet->getStyle("E1:E" . $sheet->getHighestRow())->getAlignment()->setWrapText(true);

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

    private function sanitizeToFilename(string $input): string
    {
        $forbidden = ["<", ">", ":", "\"", "\/", "\\", "|", "?", "*"];
        return str_replace($forbidden, "_", $input);
    }
}
