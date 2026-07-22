<?php

namespace App\Twig;

use Pimcore\Model\Notification\Service\UserService;
use Pimcore;
use Pimcore\Security\User\UserLoader;
use Symfony\Bundle\SecurityBundle\Security;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;

class MemberExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(private readonly UserLoader $userLoader)
    {

    }

    public function getGlobals(): array
    {
        $user = $this->userLoader->getUser();

        if (!$user instanceof Pimcore\Model\User)
        {
            return [
                'member' => null
            ];
        }

        return [
            'member' => Pimcore\Model\DataObject\User::getByUser($user->getId())->current()
        ];
    }
}
