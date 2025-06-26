<?php

namespace App\Twig;

use Pimcore\Model\DataObject\Classificationstore;
use Pimcore\Model\DataObject\Classificationstore\KeyConfig;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class KeyNameExtension extends AbstractExtension
{
    public function getFunctions() : array
    {
        return [
            new TwigFunction('keyname', [$this, 'keyname'], ['is_safe' => ['html']]),
            new TwigFunction('keyConfig', [$this, 'keyConfig'], ['is_safe' => ['html']]),
        ];
    }

    public function keyname(int $keyId): string
    {
        $k = Classificationstore\KeyConfig::getById($keyId);
        if(!$k)
        {
            throw new \Exception('Key configuration not found');
        }

        return $k->getTitle();
    }

    public function keyConfig(int $keyId): KeyConfig
    {
        $k = Classificationstore\KeyConfig::getById($keyId);

        if(!$k)
        {
            throw new \Exception('Key configuration not found');
        }

        return $k;
    }
}
