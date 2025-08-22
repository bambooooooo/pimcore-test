<?php

namespace App\Feed\Writer;

use PhpParser\Node\ClosureUse;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Offer;
use Pimcore\Model\DataObject\Product;
use Pimcore\Model\DataObject\ProductSet;
use Closure;

class XmlFeedWriter
{
    /**
     * @param Offer $offer
     * @param Closure(Product|ProductSet):string $formatter
     */
    public function __construct(private readonly Offer $offer, private readonly Closure $formatter)
    {

    }

    private function feed($stream)
    {
        $objects = $this->offer->getDependencies()->getRequiredBy();

        $data = [];

        foreach ($objects as $object)
        {
            if($object['type'] == 'object')
            {
                $obj = DataObject::getById($object['id']);

                if($obj instanceof Product || $obj instanceof ProductSet)
                {
                    fwrite($stream, ($this->formatter)($obj));
                }
            }
        }

        return $data;
    }

    public function write($stream)
    {
        $this->begin($stream);
        $this->feed($stream);
        $this->end($stream);
    }

    private function begin($stream): void
    {
        $now = date('d.m.Y H:i:s');
        fwrite($stream, "<?xml version=\"1.0\" encoding=\"UTF-8\"?><products version=\"$now\">");
    }

    private function end($stream): void
    {
        fwrite($stream, "</products>");
    }
}
