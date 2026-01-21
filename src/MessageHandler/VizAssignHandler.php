<?php

namespace App\MessageHandler;

use App\Message\VizAssignMessage;
use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Image;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Data\Hotspotimage;
use Pimcore\Model\DataObject\ProductSet;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class VizAssignHandler
{
    private array $set_viz;

    public function __construct(private readonly array $viz_set, private readonly string $viz_root_path)
    {
        $this->set_viz = [];
        foreach ($this->viz_set as $viz => $sets)
        {
            foreach($sets as $set)
            {
                $this->set_viz[$set] = $viz;
            }
        }
    }

    public function __invoke(VizAssignMessage $message)
    {
        if($message->getType() == 'ProductSet')
        {
            $this->assignImagesToNewProductSet($message);
        }
        else
        {
            $this->assignImageToExisitngSets($message);
        }
    }

    private function assignImagesToNewProductSet(VizAssignMessage $message)
    {
        $obj = ProductSet::getById($message->getId());

        $keyParts = explode("-", $obj->getKey());
        if(count($keyParts) != 3)
        {
            return;
        }

        $collection = $keyParts[0];
        $color = $keyParts[1];
        $no = $keyParts[2];

        $changed = false;

        $bt = $collection . "-" . $color . "-SET-PACKSHOT-BT." . str_pad($no, 4, "0", STR_PAD_LEFT) . ".png";
        $btp = $collection . "-" . $color . "-SET-PACKSHOT-BTP." . str_pad($no, 4, "0", STR_PAD_LEFT) . ".png";

        $btpImage = Image::getByPath($this->viz_root_path . '/' . $btp);
        if($btpImage)
        {
            $obj->setImage($btpImage);
            $changed = true;
        }

        $imgs = [];
        $btImage = Image::getByPath($this->viz_root_path . '/' . $bt);
        if($btImage)
        {
            $imgs[] = new DataObject\Data\Hotspotimage($btImage);
        }

        if(array_key_exists($no, $this->set_viz))
        {
            $isLike = $collection . "-" . $color . "-" . $this->set_viz[$no] . "-SET%.jpg";

            $images = new \Pimcore\Model\Asset\Listing();
            $images->setCondition("filename LIKE ? AND parentid = ?", [
                $isLike, Asset::getByPath($this->viz_root_path)->getId()]
            );
            $images->load();

            foreach ($images as $image)
            {
                $imgs[] = new DataObject\Data\Hotspotimage($image);
            }

            usort($imgs, function (Hotspotimage $a, Hotspotimage $b) {

                if(strpos($a->getImage()->getKey(), '-PACKSHOT-BT'))
                    return -1;

                if(strpos($b->getImage()->getKey(), '-PACKSHOT-BT'))
                    return 1;

                return strcmp($a->getImage()->getKey(), $b->getImage()->getKey());
            });
        }

        if($imgs)
        {
            $obj->setImages(new DataObject\Data\ImageGallery($imgs));
            $changed = true;
        }

        if($changed)
        {
            $obj->save();
        }
    }

    private function assignImageToExisitngSets(VizAssignMessage $message)
    {
        /** @var Image $asset */
        $asset = Asset::getById($message->getId());

        if(preg_match('/^(?<collection>\w+)-(?<color>\w+)-(?<no>\d+)-SET\.(?<imageno>\d{4})\.(jpg|jpeg|png)$/i', $asset->getKey(), $chunksViz))
        {
            if(!array_key_exists($chunksViz['no'], $this->viz_set))
            {
                return;
            }

            foreach($this->viz_set[$chunksViz['no']] as $setNo)
            {
                $sets = new DataObject\ProductSet\Listing();
                $sets->setCondition("`key` = ?", [$chunksViz['collection'] . "-" . $chunksViz['color'] . "-" . $setNo]);
                $sets->setUnpublished(true);
                $sets->load();

                foreach($sets as $set)
                {
                    $this->addSetImage($set, $asset);
                }
            }

            return;
        }

        if(preg_match('/^(?<collection>\w+)-(?<color>\w+)-SET-PACKSHOT-(BT)\.(?<setno>\d{4})\.(jpg|jpeg|png)$/i', $asset->getKey(), $chunksVizBT))
        {
            $setKey = $chunksVizBT['collection'] . "-" . $chunksVizBT['color'] . "-" . substr($chunksVizBT['setno'], 2);

            $sets = new DataObject\ProductSet\Listing();
            $sets->setCondition("`key` = ?", [$setKey]);
            $sets->setUnpublished(true);
            $sets->load();

            foreach ($sets as $set)
            {
                $this->addSetImage($set, $asset);
            }

            return;
        }

        if(preg_match('/^(?<collection>\w+)-(?<color>\w+)-SET-PACKSHOT-(BTP)\.(?<setno>\d{4})\.(jpg|jpeg|png)$/i', $asset->getKey(), $chunksMainBTP))
        {
            $setKey = $chunksMainBTP['collection'] . "-" . $chunksMainBTP['color'] . "-" . substr($chunksMainBTP['setno'], 2);

            $sets = new DataObject\ProductSet\Listing();
            $sets->setCondition("`key` = ?", [$setKey]);
            $sets->setUnpublished(true);
            $sets->load();

            foreach ($sets as $set)
            {
                $set->setImage($asset);
                $set->save();
            }
        }
    }

    private function addSetImage(ProductSet $set, Image $asset): void
    {
        /** @var Hotspotimage $img */
        foreach ($set->getImages() as $img)
        {
            if($img->getImage()->getId() == $asset->getId())
            {
                return;
            }
        }

        $images = $set->getImages()->getItems();
        $images[] = new Hotspotimage($asset);

        usort($images, function (Hotspotimage $a, Hotspotimage $b) {

            if(strpos($a->getImage()->getKey(), '-PACKSHOT-BT'))
                return -1;

            if(strpos($b->getImage()->getKey(), '-PACKSHOT-BT'))
                return 1;

            return strcmp($a->getImage()->getKey(), $b->getImage()->getKey());
        });

        $set->setImages(new DataObject\Data\ImageGallery($images));
        $set->save();
    }
}
