<?php

namespace App\Controller;

use App\Feed\Writer\XmlFeedWriter;
use App\Feed\XmlFeedMeb24;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;

class FeedController extends FrontendController
{
    #[Route('/feed/{id}', name: 'feed')]
    public function feedAction(Request $request): Response
    {
        $offer = Offer::getById($request->get('id'));

        if(!$offer)
        {
            return new Response("Offer not found", Response::HTTP_NOT_FOUND);
        }

        $download = $request->get("download") ?? false;

        $fw = new XmlFeedMeb24($offer);

        $response = new StreamedResponse(function () use ($fw) {
            $tmp = tempnam(sys_get_temp_dir(), 'feed_');
            $s = fopen($tmp, 'w+b');
            $fw->writer->write($s);
            readfile($tmp);
        });

        $response->headers->set('Content-Type', 'text/xml');

        if($download)
        {
            $fname = "feeed_megstyl_" . date("y.m.d-H.i.s") . ".xml";
            $response->headers->set('Content-Disposition', 'attachment; filename="' . $fname . '"');
        }

        $response->setStatusCode(Response::HTTP_OK);

        return $response;
    }
}
