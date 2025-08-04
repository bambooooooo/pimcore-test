<?php

namespace App\EventListener;

use CzProject\PdfRotate\PdfRotate;
use Pimcore\Event\Model\AssetEvent;
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

    private function rotateElementSheetLeft($f)
    {
        $pdf = new PdfRotate;
        $pdf->rotatePdf($f, $f, PdfRotate::DEGREES_270);
    }
}
