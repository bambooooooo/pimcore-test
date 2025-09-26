<?php

namespace App\Model\AdminStyle;

use Pimcore\Model\Element\AdminStyle;
use Pimcore\Model\Element\ElementInterface;

class Order extends AdminStyle
{
    protected \Pimcore\Model\DataObject\Order $order;
    public function __construct(protected readonly ElementInterface $element)
    {
        parent::__construct($element);

        /** @var \Pimcore\Model\DataObject\Order $element */
        $this->order = $element;

        if($this->order->getDone())
        {
            $this->elementIcon = "/bundles/pimcoreadmin/img/flat-color-icons/ok.svg";
        }
    }

    public function getElementQtipConfig(): ?array
    {
        $title = "<strong>" . $this->order->getKey() . "</strong>";
        $text = "Id: " . $this->order->getId();

        $MAX_LINES = 5;

        if($this->order->hasChildren())
        {
            $items = "<br/>";
            $shown = 0;
            $break = false;

            foreach ($this->order->getChildren() as $serie)
            {
                foreach ($serie->getProducts() as $lip)
                {
                    $items .= $lip->getElement()->getKey() . " x" . $lip->getQuantity() . "<br/>";

                    $shown++;
                    if($shown >= $MAX_LINES)
                    {
                        $break = true;
                        break;
                    }
                }

                if($break)
                    break;
            }

            if($shown >= $MAX_LINES)

            $text .= $items;
        }
        else
        {
            if($this->order->getProducts())
            {
                $items = "<br/>";

                $shown = 0;

                foreach ($this->order->getProducts() as $lip)
                {
                    $items .= $lip->getElement()->getKey() . " x" . $lip->getQuantity() . "<br/>";

                    $shown++;
                    if($shown >= $MAX_LINES)
                    {
                        break;
                    }
                }
            }
            else
            {
                $items = "<br/>No products found";
            }

            $text .= $items;
        }

        return [
            "title" => $title,
            "text" => $text
        ];
    }
}
