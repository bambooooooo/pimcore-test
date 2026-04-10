<?php

namespace App\EventListener;

use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\Asset;

class GridExportListener {
    public function postCsvItemExport(DataObjectEvent $event): void
    {
        if(!array_key_exists("image", $event->getArgument('context')))
        {
            return;
        }

        $objectData = $event->getArgument('objectData');

        foreach ($objectData as $key => $value) {
            if($asset = Asset::getByPath($value))
            {
                if($asset->getType() == 'image')
                {
                    $objectData[$key] = "![$key]($value)";
                }
            }
        }

        $event->setArgument('objectData', $objectData);
    }
}
