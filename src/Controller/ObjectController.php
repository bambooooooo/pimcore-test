<?php

namespace App\Controller;

use App\OptionProvider\ProductPriceLevelProvider;
use App\Service\DeepLService;
use App\Service\PriceLevelService;
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
use Symfony\Component\Finder\Finder;
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
                                private readonly CacheItemPoolInterface $cache,
                                private readonly PriceLevelService $priceLevelService)
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

            $productListing = new DataObject\Product\Listing();
            $productListing->setCondition("Groups like '%," . $obj->getId() . ",%' AND `ObjectType`='ACTUAL' ");

            $prods = $productListing->load();

            foreach ($prods as $product)
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

            $setListing = new DataObject\ProductSet\Listing();
            $setListing->setCondition("Groups like '%," . $obj->getId() . ",%' ");
            $sets = $setListing->load();

            foreach ($sets as $set)
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
    public function oldDataSheet(Request $request): Response
    {
        return $this->redirectToRoute('_price_list', $request->request->all());
    }

    #[Route('/object/{_locale}/{id}/price-list', name: '_price_list', defaults: ['_locale' => 'pl', 'locale' => 'pl'])]
    public function datasheetAction(Request $request): Response
    {
        $type = $request->get("type") ?? "pdf"; // pdf, pdf-spec, xlsx
        $preview = filter_var($request->get("preview") ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);;
        $priceLevel = $request->get("price_level");
        $showProductStocks = filter_var($request->get("show_product_stocks") ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $showUnpublished = filter_var($request->get("show_unpublished") ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $showProducts = filter_var($request->get("show_products") ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $showSets = filter_var($request->get("show_sets") ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $showRelatedProducts = filter_var($request->get("show_related_products") ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $showProductsTypeSKU = filter_var($request->get("show_products_type_sku") ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $showPrices = filter_var($request->get("show_prices") ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
        $showSummaryGrid = filter_var($request->get("show_summary_grid") ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

        $productTypes = $showProductsTypeSKU ? "'ACTUAL', 'SKU'" : "'ACTUAL'";
        $itemStatuses = "'Active','Sale'";

        $params = [
            'sets_row_cnt' => $request->query->get("sets") ?? 5,
            'products_row_cnt' => $request->query->get("products") ?? 5,
            'price_level' => $priceLevel,
            'show_prices' => $showPrices,
            'show_product_stocks' => $showProductStocks,
            "new_after_date" => (int)$request->query->get("new") ?? 1,
            'show_summary_grid' => $showSummaryGrid
        ];

        DataObject::setHideUnpublished(!$showUnpublished);

        $group = DataObject\Group::getById($request->get('id'));
        if(!$group)
            return new Response("DataObject not found", Response::HTTP_NOT_FOUND);

        if($showPrices && $priceLevel && !DataObject\ClassDefinition::getById('Product')->getFieldDefinition($priceLevel))
        {
            return new Response("Price level price not found", Response::HTTP_NOT_FOUND);
        }

        $products = $showProducts ? $this->getProductsForPriceList($group, $productTypes, $itemStatuses, $priceLevel) : [];
        $sets = $showSets ? $this->getSetsForPriceList($group, $itemStatuses, $priceLevel) : [];
        $related = $showRelatedProducts ? $this->getRelatedProductsForPriceList($sets, $products, $priceLevel, $showUnpublished, $showProductsTypeSKU) : [];

        if(!$products && !$sets && !$related)
        {
            return new Response("No items found.", Response::HTTP_NOT_FOUND);
        }

        if($type == 'xlsx')
        {
            return $this->getSheetPricesXlsx($group, $products, $sets, $related, $priceLevel);
        }

        if($type == 'pdf-spec')
        {
            $html = $this->renderView('factory/pdf/datasheet.html.twig', array_merge($params, [
                'group' => $group,
                'prods' => $products,
                'sets' => $sets,
                'related' => $related,
            ]));
        }
        else
        {
            $html = $this->renderView('factory/pdf/price_list.html.twig', array_merge($params, [
                'group' => $group,
                'prods' => $products,
                'sets' => $sets,
                'related' => $related,
            ]));
        }

        if($preview)
        {
            return new Response($html, 200);
        }

        return $this->getPriceListPdf($group, $html);
    }

    private function getProductsForPriceList(Group $group, string $productTypes, string $itemStatuses, string $priceLevel = null, $orderBy = 'key'): array
    {
        $productListing = new DataObject\Product\Listing();
        $cond = "Groups like '%," . $group->getId() . ",%' AND `ObjectType` IN (" . $productTypes . ") AND `Status` IN (" . $itemStatuses . ")";
        if($priceLevel)
        {
            $cond .= " AND `{$priceLevel}__value` > 0";
        }

        $productListing->setCondition($cond);
        $prods = $productListing->load();

        usort($prods, function (DataObject\Product $a, DataObject\Product $b) use ($orderBy) {

            if($orderBy == 'name')
            {
                return strcmp($a->getName() ?? $a->getKey(), $b->getName() ?? $b->getKey());
            }

            return strcmp($a->getKey(), $b->getKey());
        });

        return $prods;
    }

    private function getSetsForPriceList(Group $group, string $itemStatuses, string $priceLevel = null, $orderBy = 'key'): array
    {
        $setListing = new DataObject\ProductSet\Listing();
        $cond = "Groups like '%," . $group->getId() . ",%' AND `Status` IN (" . $itemStatuses . ")";
        if($priceLevel)
        {
            $cond .= " AND `{$priceLevel}__value` > 1 ";
        }
        $setListing->setCondition($cond);
        $sets = $setListing->load();

        usort($sets, function ($a, $b) use ($orderBy) {

            if($orderBy == 'baseprice')
            {
                return $a->getBasePrice()->getValue() > $b->getBasePrice()->getValue();
            }

            return strcmp($a->getName() ?? $a->getKey(), $b->getName() ?? $b->getKey());
        });

        return $sets;
    }

    private function getRelatedProductsForPriceList($sets, $products, $priceLevel, $showUnpublished, $showProductsTypeSKU, $orderBy = 'key'): array
    {
        $related = [];

        foreach($sets as $set)
        {
            foreach($set->getSet() as $lip)
            {
                $product = $lip->getElement();
                if(!$showUnpublished && !$product->getPublished())
                    continue;

                if(in_array($product->getStatus(), ['Active', 'Sale'])
                    && in_array($product->getObjectType(), $showProductsTypeSKU ? ['ACTUAL', 'SKU'] : ['ACTUAL']))
                {
                    if(!in_array($product, $products))
                    {
                        $related[] = $product;
                    }
                }
            }
        }

        $related = array_unique($related);

        usort($related, function (DataObject\Product $a, DataObject\Product $b) use ($orderBy) {

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

            return strcmp($a->getName() ?? $a->getKey(), $b->getName() ?? $b->getKey());
        });

        return $related;
    }

    private function getPriceListPdf(Group $group, string $html): Response
    {
        $params = [
            'paperWidth' => '210mm',
            'paperHeight' => '297mm',
            'marginTop' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0,
            'marginRight' => 0,
            "displayHeaderFooter" => true,
            'metadata' => [
                'Title' => $group->getKey(),
                'Author' => 'pim'
            ]
        ];

        $fileName = strtoupper(implode('-', [
            $group->getName() ?? $group->getKey(),
            date("d-m-Y"),
        ])) . ".pdf";

        $adapter = \Pimcore\Bundle\WebToPrintBundle\Processor::getInstance();

        $pdf = $adapter->getPdfFromString($html, $params);

        return new Response($pdf, Response::HTTP_OK, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename=' . $fileName]);
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

    #[Route("/object/compute-catalog-price", name: "update_prices")]
    public function basePriceAction(Request $request): Response
    {
        $BASE_PRICE_TO_CATALOG_PRICE_FACTOR = 3.0;
        $PLN_TO_EUR_RATE_FACTOR = 0.97;

        DataObject::setHideUnpublished(false);

        $id = $request->get("id");

        $obj = DataObject::getById($id);

        if(!$obj instanceof Product) {
            return new Response("Object type not supported", Response::HTTP_NOT_IMPLEMENTED);
        }

        if($obj->getBase() && $obj->getBase()->getValue())
        {
            $basePrice = $obj->getBase()->getValue();
            $PLN = DataObject\QuantityValue\Unit::getById("PLN");
            $EUR = DataObject\QuantityValue\Unit::getById("EUR");

            $pricePLN = $this->priceLevelService->prettyRoundPrice($basePrice * $BASE_PRICE_TO_CATALOG_PRICE_FACTOR);
            $priceEUR = $this->priceLevelService->prettyRoundPrice($basePrice * $BASE_PRICE_TO_CATALOG_PRICE_FACTOR / ($EUR->getFactor() * $PLN_TO_EUR_RATE_FACTOR));

            $obj->setPrice_catalog_pln(new DataObject\Data\QuantityValue($pricePLN, $PLN));
            $obj->setprice_catalog_eur(new DataObject\Data\QuantityValue($priceEUR, $EUR));

            $obj->save(['versionNote' => 'Update catalog prices']);

            return new JsonResponse(["status" => "success"]);
        }

        return new JsonResponse(["status" => "success"]);
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

    #[Route("/price-levels", name: "_price_levels_head")]
    public function getPriceLevelsHead(): JsonResponse
    {
        $ret = [
            'data' => []
        ];
        $ret['data'][] = [
            'name' => null,
            'title' => '(null)'
        ];

        foreach($this->priceLevelService->getPriceLevels() as $name => $title)
        {
            $ret['data'][] = [
                'name' => $name,
                'title' => $title
            ];
        }

        return new JsonResponse($ret, Response::HTTP_OK);
    }

    #[Route("/labelsize", name: "get_labelsize")]
    public function getLabelSizeList()
    {
        $ret = [
            'data' => []
        ];

        $dirs = (new Finder())
                ->directories()
                ->depth('== 0')
                ->sortByName()
                ->in($this->getParameter('kernel.project_dir') . '/templates/factory/labels');

        foreach ($dirs as $dir) {
            $ret['data'][] = [
                'name' => $dir->getFilename()
            ];
        }

        return new JsonResponse($ret, Response::HTTP_OK);
    }

    #[Route("/userlabel", name: "get_userlabel")]
    public function getUserWithLabel()
    {
        DataObject::setHideUnpublished(false);
        $ret = [
            'data' => []
        ];

        $users = new DataObject\User\Listing();
        $users->setCondition("`PackageTemplate` IS NOT NULL AND `PackageTemplate` <> ''");
        $users->setOrderKey('key');
        $users->setOrder('ASC');

        foreach ($users as $user) {
            $ret['data'][] = [
                'id' => $user->getId(),
                'name' => $user->getKey(),
            ];
        }

        return new JsonResponse($ret, Response::HTTP_OK);
    }

    #[Route('/packageproducts/{id}', name: "get_packageproducts")]
    public function getPackageProducts(Request $request): JsonResponse
    {
        DataObject::setHideUnpublished(false);
        $packageId = (int)$request->get("id");

        $ret = [
            'data' => []
        ];

        $prods = new DataObject\Product\Listing();
        $prods->setCondition("`Packages` LIKE '%,{$packageId},%'");

        foreach ($prods as $prod) {
            $ret['data'][] = [
                'id' => $prod->getId(),
                'name' => $prod->getKey() . " - " . $prod->getId(),
                'packagesCount' => count($prod->getPackages() ?? []),
            ];
        }

        usort($ret['data'], function ($a, $b) {
            $cmp = $a['packagesCount'] <=> $b['packagesCount'];
            return $cmp !== 0 ? $cmp : strcmp($a['name'], $b['name']);
        });

        return new JsonResponse($ret, Response::HTTP_OK);
    }

    private function getSheetPricesXlsx(Group $group, array $items, array $sets, array $related, string $priceLevel): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($this->translator->trans('Products'));

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
            $getter = "get" . ucfirst($priceLevel) . "_";
            $price = $obj->$getter();

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

        $fileName = strtoupper(implode('-', [
                $group->getName() ?? $group->getKey(),
                date("d-m-Y")
            ])) . ".xlsx";

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
