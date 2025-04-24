<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Element\AdminStyle;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\Asset;

class Document extends AdminStyle
{
    protected ElementInterface $element;
    public function __construct(ElementInterface $element)
    {
        parent::__construct($element);
        $this->element = $element;
    }

    public function getElementQtipConfig(): ?array
    {
        /** @var Asset\Document $doc */
        $doc = $this->element;

        $title = '<strong>' . $doc->getKey() . '</strong>';
        $text = "Id: " . $doc->getId();

        $text .= '<div><img src="' . $doc->getImageThumbnail("200x200_center_whitebg") . '" width="190" height="190"/></div>';

        $text .= '<div>' . $doc->getFileSize(true, 2) . '</div>';

        return [
            "title" => $title,
            "text" => $text
        ];
    }
}
