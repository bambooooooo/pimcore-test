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
            if(!$element->getSet())
            {
                $this->elementIcon = '/UI/4-squares.svg';
            }
            else
            {
                foreach($element->getSet() as $lip)
                {
                    /** @var DataObject\Product $p */
                    $p = $lip->getObject();
                    if($p->getObjectType() != 'ACTUAL')
                    {
                        $this->elementIcon = '/UI/4-squares.svg';
                        break;
                    }
                }
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
                $text .= '<div>';

                foreach ($set->getSet() as $li)
                {
                    if($li->getElement()->getImage())
                    {
                        $text .= '<img style="margin-right: 4px" src="' . $li->getElement()->getImage()->getThumbnail("50x50") . '" height="40" />';
                    }
                }

                $text .= '</div>';

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
