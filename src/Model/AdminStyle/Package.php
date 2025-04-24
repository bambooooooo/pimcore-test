<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Element\AdminStyle;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\DataObject;

class Package extends AdminStyle
{
    protected ElementInterface $element;
    public function __construct(ElementInterface $element)
    {
        parent::__construct($element);

        $this->element = $element;

        DataObject\Service::useInheritedValues(true, function() use ($element) {
            if($element->getObjectType() == 'VIRTUAL')
            {
                $this->elementIcon = '/UI/package-virtual.svg';
            }
        });
    }

    public function getElementQtipConfig(): ?array
    {
        /** @var DataObject\Package $package */
        $package = $this->element;

        return DataObject\Service::useInheritedValues(true, function() use ($package) {
            $title = "<strong>" . $package->getKey() . "</strong>";
            $text = "Id: " . $package->getId();

            if($package->getModel())
            {
                $text .= "<div>" . $package->getModel() . "</div>";
            }

            $dims = [];

            if($package->getWidth() and $package->getWidth()->getValue())
                $dims[] = $package->getWidth();

            if($package->getHeight() and $package->getHeight()->getValue())
                $dims[] = $package->getHeight();

            if($package->getDepth() and $package->getDepth()->getValue())
                $dims[] = $package->getDepth();

            if(count($dims) > 0)
            {
                $text .= '<div>' . implode(' x ', $dims) . '</div>';
            }

            if($package->getMass() and $package->getMass()->getValue())
            {
                $text .= "<div>" . $package->getMass() . "</div>";
            }

            return [
                "title" => $title,
                "text" => $text
            ];
        });
    }
}
