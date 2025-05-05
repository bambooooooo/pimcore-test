<?php

namespace App\Controller;

use Pimcore\Controller\FrontendController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

#[Route('/factory/{_locale}', name: 'factory_', defaults: ['_locale' => 'en', 'locale' => 'en'])]
class FactoryController extends FrontendController
{
    #[Route('', name: 'home')]
    public function defaultAction(Request $request): Response
    {
        return $this->render("factory/layouts/default.html.twig");
    }

    #[Route('/login', name: 'login')]
    public function loginAction(
        Request $request,
        AuthenticationUtils $authenticationUtils,
        UserInterface $user = null
    ): Response
    {
        if($user && $this->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('factory_home');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('factory/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logoutAction()
    {
        return $this->redirectToroute('factory_login');
    }
}
