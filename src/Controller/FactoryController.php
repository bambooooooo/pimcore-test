<?php

namespace App\Controller;

use Carbon\Carbon;
use App\Model\DataObject\User;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\Asset;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;

#[Route('/factory/{_locale}', name: 'factory_', defaults: ['_locale' => 'en', 'locale' => 'en'])]
class FactoryController extends FrontendController
{
    public function __construct(private Environment $twig)
    {

    }

    #[Route('', name: 'home')]
    public function defaultAction(Request $request): Response
    {
        return $this->render("factory/layouts/default.html.twig");
    }

    #[Route('/{id}', name: 'tree', requirements: ['id' => '\d+'])]
    public function treeAction(Request $request): Response
    {
        $grid = $request->cookies->getString('grid_style', 'list');

        $newStyle = $request->get("style");
        if($newStyle == 'list')
        {
            $grid = 'list';
        }
        elseif($newStyle == 'gallery')
        {
            $grid = 'gallery';
        }

        DataObject::setHideUnpublished(false);
        $obj = DataObject::getById($request->get('id'));
        if(!$obj)
            return new Response("Not found", Response::HTTP_NOT_FOUND);

        $data = [
            'obj' => $obj,
            'style' => $grid,
            'title' => $obj->getKey()
        ];

        $response = $this->render("factory/tree.html.twig", $data);
        $response->headers->setCookie(new Cookie("grid_style", $grid, time() + (86400 * 30)));

        return $response;
    }

    #[Route('/search', name: 'search')]
    public function searchAction(Request $request): Response
    {
        DataObject::setHideUnpublished(false);

        $search = $request->query->get('search');

        $objects = [];
        $assets = [];

        $kind = $request->query->all('kind');

        if($search)
        {
            $id = intval($search);

            $obj = DataObject::getById($id);

            if($obj)
            {
                return $this->redirectToRoute('factory_tree', ['id' => $id]);
            }
        }

        if($search && $kind)
        {
            $allowedObjectClasses = ['product', 'package', 'group'];
            if(array_intersect($allowedObjectClasses, $kind))
            {
                $objectCls = [];
                if(in_array('product', $kind))
                    $objectCls[] = "'Product'";

                if(in_array('package', $kind))
                    $objectCls[] = "'Package'";

                if(in_array('group', $kind))
                    $objectCls[] = "'Group'";

                $objects = new DataObject\Listing();
                $objects->setCondition("UPPER(`key`) LIKE UPPER('%" . $search . "%') AND `className` IN (".implode(',', $objectCls).")");

                $objects->load();
            }

            $allowedAssetClasses = ['pdf', 'image'];

            if(array_intersect($allowedAssetClasses, $kind))
            {
                $assetCls = [];
                if(in_array('pdf', $kind))
                    $assetCls[] = "'document'";

                if(in_array('image', $kind))
                    $assetCls[] = "'image'";

                $where = implode(', ', $assetCls);

                $assets = new Asset\Listing();
                $assets->setCondition("UPPER(`filename`) LIKE UPPER('%" . $search . "%') AND `type` IN (" . $where . ")");

                $assets->load();
            }
        }

        return $this->render("factory/search.html.twig", [
            'error' => "",
            'objects' => $objects,
            'assets' => $assets,
        ]);
    }

    #[Route('/{id}/datasheet', name: 'datasheet')]
    public function datasheetAction(Request $request): Response
    {
        $obj = DataObject::getById($request->get('id'));
        if(!$obj)
            return new Response("Not found", Response::HTTP_NOT_FOUND);

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
            $productListing->setCondition("Groups like '%," . $obj->getId() . ",%' AND `ObjectType`='ACTUAL' AND `published` = 1 ");
            $prods = $productListing->load();

            usort($prods, function (DataObject\Product $a, DataObject\Product $b) {

                if($a->getGroup() == null || $b->getGroup() == null)
                    return 0;

                $comp = strcmp($a->getGroup()->getKey(), $b->getGroup()->getKey());

                if($comp === 0)
                {
                    return strcmp($a->getKey(), $b->getKey());
                }

                return $comp;
            });

            $setListing = new DataObject\ProductSet\Listing();
            $setListing->setCondition("Groups like '%," . $obj->getId() . ",%' AND `published` = 1 ");
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

            usort($common, function (DataObject\Product $a, DataObject\Product $b) {

                if($a->getGroup() == null || $b->getGroup() == null)
                    return 0;

                $comp = strcmp($a->getGroup()->getKey(), $b->getGroup()->getKey());

                if($comp === 0)
                {
                    return strcmp($a->getKey(), $b->getKey());
                }

                return $comp;
            });

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

    #[Route('/schedule', name: 'schedule')]
    public function scheduleAction(Request $request, UserInterface $user): Response
    {
        $orders = new DataObject\Order\Listing();

        $root = DataObject::getByPath("/ZLECENIA/PRODUKCJA");

        if(!$root)
        {
            return new Response("Schedule root /ZLECENIA/PRODUKCJA not found", Response::HTTP_NOT_FOUND);
        }

        $rootId = $root->getId();

        if($request->query->get('y') && $request->query->get('m'))
        {
            $y = (int)$request->query->get('y');
            $m = (int)$request->query->get('m');

            $orders->setCondition("YEAR(`Date`) = $y AND MONTH(`Date`) = $m AND parentid = $rootId");
        }
        else
        {
            $orders->setCondition("parentid = ?", [$rootId]);
        }

        $userData = DataObject\User::getById($user->getId());
        if($userData->getSchedule_show_unpublished_orders())
        {
            DataObject::setHideUnpublished(false);
        }

        $orders->setOrderKey("Date");
        $orders->setOrder("ASC");
        $orders->load();

        $queue = [];

        foreach ($orders as $order)
        {
            $y = $order->getDate()->year ?? 0;
            $m = $order->getDate()->month ?? 0;
            $queue[$y][$m][] = $order;
        }

        $type = $request->get("type") ?? "preview";

        if($type == "pdf")
        {
            $pdfPageParams = [
                'paperWidth' => '210mm',
                'paperHeight' => '297mm',
                'marginTop' => '3mm',
                'marginBottom' => '3mm',
                'marginLeft' => '3mm',
                'marginRight' => '3mm',
                'metadata' => [
                    'Title' => "Harmonogram",
                    'Author' => 'pim'
                ]
            ];

            $html = $this->renderView('factory/schedule.pdf.twig', [
                'queue' => $queue,
                'type' => $type,
            ]);

            $adapter = \Pimcore\Bundle\WebToPrintBundle\Processor::getInstance();
            $pdf = $adapter->getPdfFromString($html, $pdfPageParams);

            return new Response($pdf, Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
        }
        else if($type == "vendor")
        {
            $pdfPageParams = [
                'paperWidth' => '210mm',
                'paperHeight' => '297mm',
                'marginTop' => '3mm',
                'marginBottom' => '3mm',
                'marginLeft' => '3mm',
                'marginRight' => '3mm',
                'metadata' => [
                    'Title' => "Harmonogram",
                    'Author' => 'pim'
                ]
            ];

            $html = $this->renderView('factory/schedule_vendor.pdf.twig', [
                'queue' => $queue,
                'type' => $type,
            ]);

            $adapter = \Pimcore\Bundle\WebToPrintBundle\Processor::getInstance();
            $pdf = $adapter->getPdfFromString($html, $pdfPageParams);

            return new Response($pdf, Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
        }

        return $this->render("factory/schedule.html.twig", [
            "queue" => $queue,
            "title" => "Harmonogram produkcyjny",
            'type' => $type,
        ]);
    }

    #[Route('/account', name: 'account')]
    public function accountAction(Request $request, UserInterface $user = null): Response
    {
        $changed = false;
        $userData = User::getById($user->getId());

        foreach($request->request->all() as $key => $value)
        {
            if(strpos($key, "theme_") === 0 && $userData->get($key) != ($value === "on"))
            {
                $userData->set($key, $value === "on");
                $changed = true;
            }
        }

        if($changed)
        {
            $userData->save();
            $this->addFlash("success", "Saved");
        }

        return $this->render("factory/account.html.twig");
    }

    #[Route('/labels/{id}', name: 'labels')]
    public function labelsAction(Request $request): Response
    {
        $format = $request->query->get('format') ?? "pdf";
        $size = $request->query->get('size') ?? "32x20"; // auto - adjust to package height
        $tplType = $request->query->get('tpl') ?? "default";
        $step = $request->query->get('step') ?? 1;
        $repeat = $request->query->get('repeat') ?? 1;
        $compatibility = $request->query->get('compatibility') ?? "model";

        $copies = $request->query->get('copies') ?? 1;

        $obj = DataObject\Package::getById($request->get('id'));
        if(!$obj)
        {
            return new Response("Not found", Response::HTTP_NOT_FOUND);
        }

        $orderId = $request->query->get('serie_id');
        $order = ($orderId) ? DataObject\Order::getById($orderId) : null;

        $productId = $request->query->get('product_id');
        $product = ($productId) ? DataObject\Product::getById($productId) : null;

        $customerId = $request->query->get('customer_id');
        $customer = ($customerId) ? DataObject\User::getById($customerId) : null;

        if($customer && $customer->getPackageTemplate())
        {
            $tplType = $customer->getPackageTemplate();
        }

        $tplPath = 'factory/labels/' . $size . '/' . $tplType . '.html.twig';

        if(!$this->twig->getLoader()->exists($tplPath))
        {
            return new Response("Template [$tplPath] not found", Response::HTTP_NOT_FOUND);
        }

        $dims = explode("x", $size);
        $w = (int)$dims[0];
        $h = (int)$dims[1];

        $pdfPageParams = [
            'paperWidth' => $w . 'mm',
            'paperHeight' => $h . 'mm',
            'marginTop' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0,
            'marginRight' => 0,
            'metadata' => [
                'Title' => $obj->getKey() . "@" . $size,
                'Author' => 'pim'
            ]
        ];

        $html = $this->renderView($tplPath, [
            'package' => $obj,
            'product' => $product,
            'order' => $order,
            'copies' => $copies,
            'step' => $step,
            'w' => $w,
            'h' => $h,
            'repeat' => $repeat,
            'compatibility' => $compatibility
        ]);

        $adapter = \Pimcore\Bundle\WebToPrintBundle\Processor::getInstance();
        $pdf = $adapter->getPdfFromString($html, $pdfPageParams);

        return new Response($pdf, Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
    }


    #[Route('/labels/elements/{id}', name: 'labels_elements')]
    public function elementLabelsAction(Request $request): Response
    {
        $packageId = (int)$request->get('id');
        $size = $request->query->get('size') ?? "20x32";
        $copies = $request->query->get('copies') ?? 1;
        $step = $request->query->get('step') ?? 1;
        $repeat = $request->query->get('repeat') ?? 1;
        $tplType = $request->query->get('tpltype') ?? "elements";

        $package = DataObject\Package::getById($packageId);
        if(!$package)
        {
            return new Response("Not found", Response::HTTP_NOT_FOUND);
        }

        $dims = explode("x", $size);
        $w = (int)$dims[0];
        $h = (int)$dims[1];

        $pdfPageParams = [
            'paperWidth' => $w . 'mm',
            'paperHeight' => $h . 'mm',
            'marginTop' => 0,
            'marginBottom' => 0,
            'marginLeft' => 0,
            'marginRight' => 0,
            'metadata' => [
                'Title' => $package->getKey() . "@" . $size,
                'Author' => 'pim'
            ]
        ];

        $tplPath = 'factory/labels/' . $size . '/' . $tplType . '.html.twig';

        $html = $this->renderView($tplPath, [
            'package' => $package,
            'copies' => $copies,
            'step' => $step,
            'w' => $w,
            'h' => $h,
            'repeat' => $repeat,
        ]);

        $adapter = \Pimcore\Bundle\WebToPrintBundle\Processor::getInstance();
        $pdf = $adapter->getPdfFromString($html, $pdfPageParams);

        return new Response($pdf, Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
    }

    #[Route('/order/move', name: 'move')]
    public function moveAction(Request $request): Response
    {
        $id = $request->get('id');
        $newDate = $request->get('newDate');
        $date = Carbon::parse($newDate);

        $o = DataObject\Order::getById($id);

        if(!$o)
        {
            return new Response("Order not found", Response::HTTP_NOT_FOUND);
        }

        $o->setDate($date);
        $o->save();

        return new Response("Changed", Response::HTTP_OK);
    }

    #[Route('/order/item/done', name: 'item_done')]
    public function orderItemDone(Request $request, UserInterface $user): Response
    {
        $userData = User::getById($user->getId());
        if(!$userData->getSchedule_mark_line_item_done())
        {
            return new Response("User has no permissions", Response::HTTP_FORBIDDEN);
        }

        $orderId = (int)$request->get('orderid');
        $productId = (int)$request->get('productid');
        $quantity= (int)$request->get('quantity');
        $done= (int)$request->get('done');

        $o = DataObject\Order::getById($orderId);
        if(!$o)
        {
            return new Response("Order not found", Response::HTTP_NOT_FOUND);
        }

        $found = false;

        foreach ($o->getProducts() as $li)
        {
            if($li->getObject()->getId() == $productId and $li->getQuantity() == $quantity)
            {
                $found = true;
                $li->setQuantityDone($done);
                $o->save();

                break;
            }
        }

        if(!$found)
            return new Response("Product not found in order", Response::HTTP_NOT_FOUND);

        return new Response("Ok", Response::HTTP_OK);
    }

    #[Route('/order/item/status', name: 'item_status')]
    public function orderItemStatusAction(Request $request, UserInterface $user): Response
    {
        $orderId = (int)$request->get('orderid');
        $productId = (int)$request->get('productid');
        $newStatus = $request->get('status');

        $o = DataObject\Order::getById($orderId);
        if(!$o)
        {
            return new Response("Order not found", Response::HTTP_NOT_FOUND);
        }

        $found = false;

        foreach ($o->getProducts() as $li)
        {
            if($li->getObject()->getId() == $productId)
            {
                $found = true;
                $li->setStatus($newStatus);
                $o->save();

                break;
            }
        }

        if(!$found)
            return new Response("Product not found in order", Response::HTTP_NOT_FOUND);

        return new Response("Ok", Response::HTTP_OK);
    }

    #[Route('/elements/{id}', name: 'elements')]
    public function serieAction(Request $request): Response
    {
        $serie = DataObject\Order::getById($request->get('id'));
        if(!$serie)
        {
            return new Response("Not found", Response::HTTP_NOT_FOUND);
        }

        return $this->render("factory/pdf/serie_elemets.html.twig", ['serie' => $serie]);
    }

    #[Route('/login', name: 'login')]
    public function loginAction(
        Request $request,
        AuthenticationUtils $authenticationUtils,
        UserInterface $user = null
    ): Response
    {
        if($user && $this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('factory_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('factory/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logoutAction()
    {
        return $this->redirectToroute('factory_login');
    }
}
