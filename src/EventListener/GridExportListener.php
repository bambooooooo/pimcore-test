<?php

namespace App\EventListener;

use Pimcore\Event\Model\DataObjectEvent;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Pimcore\Model\DataObject\Data\ImageGallery;

class GridExportListener {
    public function postCsvItemExport(DataObjectEvent $event): void
    {
        if(!array_key_exists("image", $event->getArgument('context')))
        {
            return;
        }

        $objectData = $event->getArgument('objectData');

        foreach ($objectData as $key => $value) {

            $gallery = $this->extractImageGalleryUrls($value);
            if(is_array($gallery))
            {
                $objectData[$key] = '[' . join(',', $gallery) . ']';
                continue;
            }

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

    function extractImageGalleryUrls(string $input): array|null
    {
        $result = [];

        $decoded = base64_decode($input);

        if(!$decoded)
        {
            return null;
        }

        if (!is_string($decoded) || !preg_match('/^(O|a|s|i|b|d):/', $decoded))
        {
            return null;
        }

        try {
            $data = unserialize($decoded, [
                'allowed_classes' => [
                    'Pimcore\Model\DataObject\Data\ImageGallery',
                    'Pimcore\Model\DataObject\Data\Hotspotimage',
                    'Pimcore\Model\Asset\Image'
                ]
            ]);
        }
        catch (\Throwable $e) {
            return null;
        }

        if(!$data instanceof ImageGallery)
        {
            return null;
        }

        $items = $data->getItems();

        if(empty($items) || !is_array($items)) {
            return [];
        }

        foreach($items as $item) {
            if(!$item instanceof Hotspotimage)
            {
                continue;
            }

            $image = $item->getImage();

            if(!$image instanceof Asset\Image)
            {
                continue;
            }

            $result[] = "![Image]({$image->getFullPath()})";
        }

        return $result;
    }
}
