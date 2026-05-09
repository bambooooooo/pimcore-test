<?php

namespace App\EventListener;

use App\Message\VizAssignMessage;
use CzProject\PdfRotate\PdfRotate;
use Pimcore\Event\Model\AssetEvent;
use Pimcore\Model\Asset\Folder;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Group;
use setasign\Fpdi;
use Symfony\Component\Messenger\MessageBusInterface;

class AssetsListener
{
    public function __construct(private readonly MessageBusInterface $messageBus, private readonly string $viz_root_path)
    {

    }

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
            /** @var Image $asset */
            $asset = $event->getAsset();

            $this->updateProductSetAssignment($asset);
            $this->updatePrestashopImage($asset);
        }
    }

    private function updateProductSetAssignment(Image $asset): void
    {
        if($asset->getVersionCount() > 1 && $asset->getParent()->getId() == Folder::getByPath($this->viz_root_path)->getId()){

            $previous = $asset->getVersions()[$asset->getVersionCount() - 2];

            $prevName = $previous->getData()->getFilename();
            $recentName = $asset->getKey();

            if($prevName != $recentName)
            {
                $this->messageBus->dispatch(new VizAssignMessage($asset->getId()));
            }
        }
    }

    private function updatePrestashopImage(Image $asset): void
    {
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

    private function rotateElementSheetLeft($f)
    {
        $pdf = new PdfRotate;
        $pdf->rotatePdf($f, $f, PdfRotate::DEGREES_270);
    }
}
