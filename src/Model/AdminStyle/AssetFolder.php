<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Asset;
use Pimcore\Model\Asset\Folder;
use Pimcore\Model\Element\AdminStyle;
use Pimcore\Model\Element\ElementInterface;

class AssetFolder extends AdminStyle
{
    protected ElementInterface $element;
    /**
     * @param Folder $element
     */
    public function __construct(Folder $element)
    {
        parent::__construct($element);
        $this->element = $element;
    }

    public function getElementQtipConfig(): ?array
    {
        $defaultQtipConfig = parent::getElementQtipConfig();

        if($this->element->getProperties())
        {
            $output = "<div style='width: 300px;'>";

            foreach($this->element->getProperties() as $property)
            {
                if($property->getType() == 'object' && method_exists($property->getData(), 'getImage'))
                {
                    /** @var \Pimcore\Model\DataObject\Product $p */
                    $p = $property->getData();
                    $output .= "<div style='width: 96px; margin: 2px; float: left;'><img src='" . $p->getImage()?->getThumbnail("200x200") . "' style='width: 96px; height: 96px;'>" . $p->getId() . "</div>";
                }
            }

            $output .= "</div>";

            return [
                'title' => $defaultQtipConfig['title'],
                'text' => $output,
            ];
        }

        return $defaultQtipConfig;
    }
}
