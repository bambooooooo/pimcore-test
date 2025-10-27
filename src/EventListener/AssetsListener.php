<?php

namespace App\EventListener;

use CzProject\PdfRotate\PdfRotate;
use Pimcore\Event\Model\AssetEvent;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Group;
use setasign\Fpdi;

class AssetsListener
{
    public function postAddEvents(AssetEvent $event): void
    {
        if($event->getAsset()->getMimeType() == 'application/pdf')
        {
            $asset = $event->getAsset();

            if(preg_match("/\w+-\w+-\w+-RT.pdf/i", $asset->getKey()))
            {
                $f = "../public/var/assets" . $asset->getPath() . $asset->getFilename();
                $pdf = new Fpdi\Fpdi;

                $pdf->setSourceFile($f);
                $size = $pdf->getTemplateSize($pdf->importPage(1));

                if($size['height'] > $size['width'])
                {
                    $this->rotateElementSheetLeft($f);
                }
            }
        }
    }

    public function postUpdateEvents(AssetEvent $event): void
    {
        if($event->getAsset()->getType() == 'image')
        {
            $asset = $event->getAsset();
            foreach ($asset->getDependencies()->getRequiredBy() as $dependency)
            {
                if($dependency['type'] == 'object')
                {
                    $obj = DataObject::getById($dependency['id']);
                    if($obj instanceof Group)
                    {
                        if($obj->getPs_megstyl_pl() && $obj->getPs_megstyl_pl_id())
                        {
                            $obj->save();
                        }
                    }
                }
            }
        }
    }

    private function rotateElementSheetLeft($f)
    {
        $pdf = new PdfRotate;
        $pdf->rotatePdf($f, $f, PdfRotate::DEGREES_270);
    }
}
