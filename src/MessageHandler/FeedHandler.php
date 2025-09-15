<?php

namespace App\MessageHandler;

use App\Feed\Writer\FeedWriter;
use App\Message\FeedMessage;
use Pimcore\Model\Asset;
use Pimcore\Model\DataObject\Data\BlockElement;
use Pimcore\Model\DataObject\Offer;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class FeedHandler
{
    public function __construct(private readonly CacheItemPoolInterface $cacheItemPool)
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

        $cacheKey = "object_status_" . $offer->getId();
        $cacheItem = $this->cacheItemPool->getItem($cacheKey);

        $cacheItem->set("Processing...");
        $cacheItem->expiresAfter(3600);
        $this->cacheItemPool->save($cacheItem);

        $cp = $this->cacheItemPool;

        $feeds = [];

        $i = 0;
        $tot = count($offer->getFeed());

        foreach ($offer->getFeed() as $feed)
        {
            $time = time();
            $cname = $feed['Schema']->getData();
            $referenceOffer = key_exists('ReferenceOffer', $feed) ? $feed['ReferenceOffer']?->getData() : null;

            echo $cname . ' - ' . $referenceOffer . PHP_EOL;

            if(!class_exists($cname))
            {
                echo "Template class [" . $cname . "] not found";
                return;
            }

            $schema = explode("\\", $cname);
            $schema = end($schema);

            /** @var FeedWriter $cname */
            $fw = new $cname($offer, $referenceOffer);

            $fw->setStatus(function ($current, $total) use ($cacheItem, $cp, $i, $tot) {

                if($current % 10 == 0)
                {
                    $percentage = round(($i * $total + $current) * 100 / ($tot * $total), 2);

                    $cacheItem->set("Processing {$percentage}%...");
                    $cacheItem->expiresAfter(3600);

                    $cp->save($cacheItem);
                    $cp->commit();
                }

                if($current == $total && ($i + 1) == $tot)
                {
                    $cp->delete($cacheItem->getKey());
                    $cp->commit();
                }
            });

            $outputFilename = "feed-" . $schema . "-" . $offer->getId();

            $tmp = tempnam(sys_get_temp_dir(), 'feed_') . "." . $fw->extension();
            $s = fopen($tmp, 'w+b');
            $fw->write($s);

            $asset = $this->saveToAsset($tmp, $outputFilename, $fw->contentType(), $fw->extension(), hashFileName: true);

            $feed['File'] = new BlockElement('File', 'manyToOneRelation', $asset);

            unlink($tmp);
            $feeds[] = $feed;
            $i++;

            $duration = time() - $time;

            echo $cname . ' - ' . $referenceOffer . ": done in " . $duration . " seconds.";
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
