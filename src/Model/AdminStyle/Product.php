<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Element\AdminStyle;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\DataObject;

class Product extends AdminStyle
{
    protected ElementInterface $element;
    public function __construct(ElementInterface $element)
    {
        parent::__construct($element);

        $this->element = $element;

        DataObject\Service::useInheritedValues(true, function() use ($element) {
            if($element->getObjectType() == 'VIRTUAL')
            {
                $this->elementIcon = '/UI/square-gray.svg';
            }
        });
    }

    public function getElementQtipConfig(): ?array
    {

        /** @var DataObject\Product $product */
        $product = $this->element;

        return DataObject\Service::useInheritedValues(true, function() use ($product) {
            $title = "<strong>" . $product->getKey() . "</strong>";
            $text = "Id: " . $product->getId();

            if($product->getName())
            {
                $text .= "<div>" . $product->getName() . "</div>";
            }

            if($product->getImage())
            {
                $thumbnail = $product->getImage()->getThumbnail("200x200");
                $text .= '<div><img src="' . $thumbnail . '" width="190" height="190"/></div>';
            }

            if($product->getModel())
            {
                $text .= "<div>" . $product->getModel() . "</div>";
            }

            $dims = [];

            if($product->getWidth() and $product->getWidth()->getValue())
                $dims[] = $product->getWidth();

            if($product->getHeight() and $product->getHeight()->getValue())
                $dims[] = $product->getHeight();

            if($product->getDepth() and $product->getDepth()->getValue())
                $dims[] = $product->getDepth();

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
