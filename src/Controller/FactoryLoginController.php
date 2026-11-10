<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Login of the factory panel (firewall "factory", see config/packages/security.yaml).
 * The session is shared with Pimcore Studio, so logging in here also logs in to Studio and vice versa.
 * Priority 10: /factory/{_locale} would otherwise swallow /factory/login.
 */
class FactoryLoginController extends AbstractController
{
    #[Route('/factory/login', name: 'factory_login', methods: ['GET', 'POST'], priority: 10)]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser())
        {
            return $this->redirectToRoute('factory_home', ['_locale' => 'pl']);
        }

        return $this->render('factory/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError(),
        ]);
    }

    #[Route('/factory/logout', name: 'factory_logout', methods: ['GET', 'POST'], priority: 10)]
    public function logout(): never
    {
        throw new \LogicException('Handled by the logout key of the factory firewall.');
    }
}
