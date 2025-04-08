<?php

namespace App\EventListener;

namespace App\EventListener;

use Pimcore\Model\Asset;
use Pimcore\Model\Element\AdminStyle;
use Pimcore\Event\BundleManager\PathsEvent;
use Pimcore\Bundle\AdminBundle\Event\ElementAdminStyleEvent;

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
}
