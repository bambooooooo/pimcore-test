<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Element\AdminStyle;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\DataObject;

class User extends AdminStyle
{
    protected ElementInterface $element;
    public function __construct(ElementInterface $element)
    {
        parent::__construct($element);
        $this->element = $element;
    }

    public function getElementQtipConfig(): ?array
    {
        /** @var DataObject\User $user */
        $user = $this->element;

        return DataObject\Service::useInheritedValues(true, function() use ($user) {
            $title = '<strong>' . $user->getKey() . '</strong>';
            $text = "Id: " . $user->getId();

            $image = $user->getImage();
            if ($image) {
                $thumbnail = $image->getThumbnail("200x200_center");
                $text .= '<p><img src="' . $thumbnail . '" width="190" height="190"/></p>';
            }

            if($user->getName())
            {
                $text .= '<p>' . $user->getName() . '</p>';
            }

            return [
                "title" => $title,
                "text" => $text
            ];
        });
    }
}
