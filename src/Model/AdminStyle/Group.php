<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Element\AdminStyle;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\DataObject;

class Group extends AdminStyle
{
    protected ElementInterface $element;
    public function __construct(ElementInterface $element)
    {
        parent::__construct($element);
        $this->element = $element;
    }

    public function getElementQtipConfig(): ?array
    {
        /** @var DataObject\Group $group */
        $group = $this->element;

        return DataObject\Service::useInheritedValues(true, function() use ($group) {
            $title = '<strong>' . $group->getKey() . '</strong>';
            $text = "Id: " . $group->getId();

            $image = $group->getImage();
            if ($image) {
                $thumbnail = $image->getThumbnail("200x200_center");
                $text .= '<p><img src="' . $thumbnail . '" width="190" height="190"/></p>';
            }

            return [
                "title" => $title,
                "text" => $text
            ];
        });
    }
}
