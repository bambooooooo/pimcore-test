<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Element\AdminStyle;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\Asset;

class Image extends AdminStyle
{
    protected ElementInterface $element;
    public function __construct(ElementInterface $element)
    {
        parent::__construct($element);
        $this->element = $element;
    }

    public function getElementQtipConfig(): ?array
    {
        /** @var Asset $im */
        $im = $this->element;

        $title = '<strong>' . $im->getKey() . '</strong>';
        $text = "Id: " . $im->getId();

        $text .= '<p><img src="' . $im . '" width="190" height="190"/></p>';

        return [
            "title" => $title,
            "text" => $text
        ];
    }
}
