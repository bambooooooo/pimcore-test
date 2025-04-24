<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Element\AdminStyle;
use Pimcore\Model\Element\ElementInterface;
use Pimcore\Model\DataObject;

class EanPool extends AdminStyle
{
    protected ElementInterface $element;
    public function __construct(ElementInterface $element)
    {
        parent::__construct($element);
        $this->element = $element;
    }

    public function getElementQtipConfig(): ?array
    {
        /** @var DataObject\EanPool $pool */
        $pool = $this->element;

        return DataObject\Service::useInheritedValues(true, function() use ($pool) {
            $title = '<strong>' . $pool->getKey() . '</strong>';
            $text = "Id: " . $pool->getId();

            $text .= "<div>Left: " . count($pool->getAvailableCodes()) . " codes</div>";

            return [
                "title" => $title,
                "text" => $text
            ];
        });
    }
}
