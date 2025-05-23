<?php

namespace App\EventListener;

namespace App\EventListener;

use Pimcore\Bundle\AdminBundle\Event\ElementAdminStyleEvent;
use Pimcore\Event\BundleManager\PathsEvent;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\EanPool;
use Pimcore\Model\DataObject\Group;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Pimcore\Model\DataObject\User;
use Pimcore\Model\DataObject\Package;
use Pimcore\Model\DataObject\LoadCarrier;

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
        else if($element instanceof ProductSet)
        {
            $event->setAdminStyle(new \App\Model\AdminStyle\ProductSet($element));
        }
        else if($element instanceof Group)
        {
            $event->setAdminStyle(new \App\Model\AdminStyle\Group($element));
        }
        else if($element instanceof EanPool)
        {
            $event->setAdminStyle(new \App\Model\AdminStyle\EanPool($element));
        }
        else if($element instanceof User)
        {
            $event->setAdminStyle(new \App\Model\AdminStyle\User($element));
        }
        else if($element instanceof Package)
        {
            $event->setAdminStyle(new \App\Model\AdminStyle\Package($element));
        }
        else if($element instanceof LoadCarrier)
        {
            $event->setAdminStyle(new \App\Model\AdminStyle\LoadCarrier($element));
        }
        else if ($element instanceof Asset\Image)
        {
            $event->setAdminStyle(new \App\Model\AdminStyle\Image($element));
        }
        else if ($element instanceof Asset\Document)
        {
            $event->setAdminStyle(new \App\Model\AdminStyle\Document($element));
        }
    }
}
