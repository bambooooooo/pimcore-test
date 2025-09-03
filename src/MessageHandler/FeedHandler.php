<?php

namespace App\MessageHandler;

use App\Feed\Writer\FeedWriter;
use App\Message\FeedMessage;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Data\BlockElement;
use Pimcore\Model\DataObject\Offer;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FeedHandler
{
    public function __construct()
    {

    }

    public function __invoke(FeedMessage $message):void
    {
        $offer = Offer::getById($message->getObjectId());

        if(!$offer)
        {
            echo "Offer not found";
            return;
        }

        $feeds = [];

        foreach ($offer->getFeed() as $feed)
        {
            $cname = $feed['Schema']->getData();
            $referenceOffer = key_exists('ReferenceOffer', $feed) ? $feed['ReferenceOffer']?->getData() : null;

            echo $cname . ' - ' . $referenceOffer . "\n";

            if(!class_exists($cname))
            {
                echo "Template class [" . $cname . "] not found";
                return;
            }

            $schema = explode("\\", $cname);
            $schema = end($schema);

            /** @var FeedWriter $cname */
            $fw = new $cname($offer, $referenceOffer);

            $outputFilename = "feed-" . $schema . "-" . $offer->getId();

            $tmp = tempnam(sys_get_temp_dir(), 'feed_') . "." . $fw->extension();
            $s = fopen($tmp, 'w+b');
            $fw->write($s);

            $asset = $this->saveToAsset($tmp, $outputFilename, $fw->contentType(), $fw->extension(), hashFileName: true);

            $feed['File'] = new BlockElement('File', 'manyToOneRelation', $asset);

            unlink($tmp);
            $feeds[] = $feed;
        }

        $offer->setFeed($feeds);
        $offer->save();
    }

    private function saveToAsset(string $sourceFile, string $fname, string $mimeType, string $extension, string $folder = "/STOCKS", bool $hashFileName = false):Asset
    {
        $dir = Asset::getByPath($folder);
        if(!$dir)
        {
            $dir = new Asset();
            $dir->setKey(ltrim($folder, "/"));
            $dir->setParentId(1);
            $dir->setType('folder');
            $dir->save();
        }

        if($hashFileName)
        {
            $fname = hash('sha256', $fname);
        }

        $fullPath = $folder . "/" . $fname . '.' . $extension;
        $asset = Asset::getByPath($fullPath);

        if(!$asset)
        {
            $asset = new Asset();
            $asset->setKey($fname . "." . $extension);
            $asset->setType('document');
            $asset->setMimeType($mimeType);
            $asset->setParentId($dir->getId());
        }

        $asset->setData(file_get_contents($sourceFile));
        $asset->save();

        $publicUrl = $asset->getFrontendPath();

        return $asset;
    }
}
