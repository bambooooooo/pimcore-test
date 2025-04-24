<?php

namespace App\EventListener;

namespace App\EventListener;

use Pimcore\Bundle\AdminBundle\Event\ElementAdminStyleEvent;
use Pimcore\Event\BundleManager\PathsEvent;
use Pimcore\Model\DataObject\Product;

class AdminStyleListener
{
    public function addJSFiles(PathsEvent $event): void
    {
        $event->setPaths(
            array_merge(
                $event->getPaths(),
                [
                    '/static/js/startup.js'
                ]
            )
        );
    }

    public function addCSSFiles(PathsEvent $event): void
    {
        $event->setPaths(
            array_merge(
                $event->getPaths(),
                [
                    '/static/css/env.css'
                ]
            )
        );
    }

    public function onResolveElementAdminStyle(ElementAdminStyleEvent $event): void
    {
        $element = $event->getElement();

        if($element instanceof Product) {
            $event->setAdminStyle(new \App\Model\AdminStyle\Product($element));
        }
    }
}
