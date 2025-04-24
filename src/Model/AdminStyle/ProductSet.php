<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Element\AdminStyle;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\DataObject;

class ProductSet extends AdminStyle
{
    protected ElementInterface $element;
    public function __construct(ElementInterface $element)
    {
        parent::__construct($element);

        $this->element = $element;

        DataObject\Service::useInheritedValues(true, function() use ($element)
        {
            if(!$element->getSet() or (count($element->getSet()) == 1 and $element->getSet()[0]->getData()['Quantity'] < 2))
            {
                $this->elementIcon = '/UI/4-squares.svg';
            }
        });
    }

    public function getElementQtipConfig(): ?array
    {

        /** @var DataObject\ProductSet $set */
        $set = $this->element;
        return DataObject\Service::useInheritedValues(true, function() use ($set){

            $title = '<strong>' . $set->getKey() . '</strong>';
            $text = "Id: " . $set->getId();

            if($set->getName())
            {
                $text .= "<div>" . $set->getName() . "</div>";
            }

            $image = $set->getImage();
            if ($image) {
                $thumbnail = $image->getThumbnail("200x200_center");
                $text .= '<div><img src="' . $thumbnail . '" width="190" height="190"/></div>';
            }

            if($set->getSet() and count($set->getSet()) > 0)
            {
                $text .= "<div>";

                foreach ($set->getSet() as $set) {
                    if($set->getQuantity() and $set->getQuantity() > 1)
                    {
                        $text .= $set->getQuantity() . "x " . $set->getElement()->getKey() . ", ";
                    }
                    else
                    {
                        $text .= $set->getElement()->getKey() . ", ";
                    }
                }

                $text = substr($text, 0, -2);

                $text .= "</div>";
            }

            return [
                "title" => $title,
                "text" => $text
            ];
        });
    }
}
