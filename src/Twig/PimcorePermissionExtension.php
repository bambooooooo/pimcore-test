<?php

namespace App\Twig;

use Pimcore\Model\DataObject;
use Pimcore\Security\User\UserLoader;
use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class PimcorePermissionExtension extends AbstractExtension
{
    public function __construct(private readonly UserLoader $userLoader)
    {

    }
    public function getTests(): array
    {
        return [
            new TwigTest('viewable', [$this, 'viewable']),
        ];
    }

    public function viewable(DataObject $object): bool
    {
        $user = $this->userLoader->getUser();


        return $user && $object->isAllowed('view', $user);
    }
}
