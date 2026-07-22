<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationSuccessHandlerInterface;
use Symfony\Component\Routing\RouterInterface;

use Pimcore\Model\User as PimcoreUser;

class LoginSuccessHandler implements AuthenticationSuccessHandlerInterface
{
    public function __construct(private readonly RouterInterface $router)
    {

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token): RedirectResponse
    {
        $user = PimcoreUser::getByName($token->getUserIdentifier());

        if((!$user->isAdmin()) && $user->isAllowed('factory'))
        {
            return new RedirectResponse($this->router->generate('factory_home'));
        }

        return new RedirectResponse($this->router->generate('pimcore_admin_index'));
    }
}
