<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Element\AdminStyle;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\DataObject;

class LoadCarrier extends AdminStyle
{
    protected ElementInterface $element;
    public function __construct(ElementInterface $element)
    {
        parent::__construct($element);

        $this->element = $element;
    }

    public function getElementQtipConfig(): ?array
    {
        /** @var DataObject\LoadCarrier $package */
        $package = $this->element;

        return DataObject\Service::useInheritedValues(true, function() use ($package) {
            $title = "<strong>" . $package->getKey() . "</strong>";
            $text = "Id: " . $package->getId();

            $image = $package->getImage();
            if ($image) {
                $thumbnail = $image->getThumbnail("200x200");
                $text .= '<p><img src="' . $thumbnail . '" width="190" height="190" alt="' . $image->getKey() . '"/></p>';
            }

            $dims = [];

            if($package->getLength()?->getValue())
                $dims[] = $package->getLength()->getValue() / 1000 . "m";

            if($package->getWidth()?->getValue())
                $dims[] = $package->getWidth()->getValue() / 1000 . "m";

            if($package->getHeight()?->getValue())
                $dims[] = $package->getHeight()->getValue() / 1000 . "m";

            if(count($dims) > 0)
            {
                $text .= '<div>' . implode(' x ', $dims) . '</div>';
            }

            return [
                "title" => $title,
                "text" => $text
            ];
        });
    }
}
