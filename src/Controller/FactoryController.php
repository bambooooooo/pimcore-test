<?php

namespace App\Controller;

use Pimcore\Bundle\AdminBundle\Controller\Admin\ElementController;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/factory/{_locale}', name: 'factory_', defaults: ['_locale' => 'en', 'locale' => 'en'])]
class FactoryController extends FrontendController
{
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
            'style' => $grid
        ];

        $response = $this->render("factory/tree.html.twig", $data);
        $response->headers->setCookie(new Cookie("grid_style", $grid, time() + (86400 * 30)));

        return $response;
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
            'metadata' => [
                'Title' => 'title',
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
            $html = $this->renderView('factory/pdf/datasheet_group.html.twig', ['group' => $obj]);
        }

        $pdf = $adapter->getPdfFromString($html, $params);

        return new Response($pdf, Response::HTTP_OK, ['Content-Type' => 'application/pdf']);
    }

    #[Route('/schedule', name: 'schedule')]
    public function scheduleAction(Request $request): Response
    {
        $orders = new DataObject\Order\Listing();

        if($request->query->get('y') && $request->query->get('m'))
        {
            $y = (int)$request->query->get('y');
            $m = (int)$request->query->get('m');

            $orders->setCondition("YEAR(`Date`) = $y AND MONTH(`Date`) = $m AND parentid = 15366");
        }
        else
        {
            $orders->setCondition("parentid = ?", [15366]);
        }

        $orders->setOrderKey("Date");
        $orders->setOrder("ASC");
        $orders->load();

        $queue = [];

        $toplan = [];

        foreach ($orders as $order) {
            if($order->getDate() == null)
            {
                $toplan[] = $order;
            }
            else
            {
                $y = $order->getDate()->year;
                $m = $order->getDate()->month;
                $queue[$y][$m][] = $order;
            }
        }

        return $this->render("factory/schedule.html.twig", [
            "queue" => $queue,
            "toplan" => $toplan,
        ]);
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
