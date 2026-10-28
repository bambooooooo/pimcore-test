<?php

namespace App\Model\Renderer;

use Pimcore\Model\DataObject\ClassDefinition\Layout\DynamicTextLabelInterface;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\Concrete;
use Pimcore\Model\DataObject\Package;

class PackageDimensionRenderer implements DynamicTextLabelInterface
{

    public function renderLayoutText(string $data, ?Concrete $object, array $params): string
    {
        if(!($object instanceof Package))
        {
            return '<div class="alert alert-danger">Unsuported object type</div>';
        }

        return DataObject\Service::useInheritedValues(true, function () use ($object) {

            /** @var Package $p */
            $p = $object;

            if(!$p->getFefco())
            {
                return ''; // no FEFCO
            }

            if($p->getFefco() == '300')
            {
                return $this->fefco300($p); // FEFCO 300 supported only
            }

            return '';
        });
    }

    private function fefco300(Package $p)
    {


        if(!$p->getWidth() || !$p->getWidth()->getValue()
            || !$p->getHeight() || !$p->getHeight()->getValue()
            || !$p->getDepth() || !$p->getDepth()->getValue())
        {
            return '';
        }

        $length = $p->getDepth()->getValue() + 45;
        $width = $p->getWidth()->getValue() + 45;
        $height = $p->getHeight()->getValue() + 15;

        $html = "<div class='alert alert-info'>
                    <h3>Sugerowane wymiary dla kartonu FEFCO 300</h3>
                    <table>
                        <tbody>
                            <tr>
                                <td>Długość:</td>
                                <td><b>$length mm</b> (+45 mm)</td>
                            </tr>
                            <tr>
                                <td>Szerokość:</td>
                                <td>$width mm (+45 mm)</td>
                            </tr>
                            <tr>
                                <td>Wysokość:</td>
                                <td>$height mm (+15 mm)</td>
                            </tr>
                        </tbody>
                    </table>
                </div>";

        return $html;

    }
}
