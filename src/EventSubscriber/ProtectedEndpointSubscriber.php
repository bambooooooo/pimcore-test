<?php

namespace App\EventSubscriber;

use App\Security\ApiTokenRegistry;
use App\Security\ProtectedEndpoint;
use Pimcore\Model\User;
use Pimcore\Tool\Authentication;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Enforces #[ProtectedEndpoint]: the request needs a Pimcore admin session or a valid API token.
 * The routes live outside the pimcore_admin firewall so their public URLs stay unchanged.
 */
class ProtectedEndpointSubscriber implements EventSubscriberInterface
{
    public const ATTR_API_CLIENT = '_api_client';
    public const ATTR_ADMIN_USER = '_admin_user';

    /** @var callable(Request): ?User */
    private $sessionAuthenticator;

    public function __construct(private readonly ApiTokenRegistry $apiTokens, ?callable $sessionAuthenticator = null)
    {
        $this->sessionAuthenticator = $sessionAuthenticator ?? Authentication::authenticateSession(...);
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::CONTROLLER => ['onController', 0]];
    }

    public function onController(ControllerEvent $event): void
    {
        if (!$event->getAttributes(ProtectedEndpoint::class))
        {
            return;
        }

        $request = $event->getRequest();

        if ($client = $this->apiTokens->getClient($request->headers->get(ApiTokenRegistry::HEADER)))
        {
            $request->attributes->set(self::ATTR_API_CLIENT, $client);
            return;
        }

        if ($user = ($this->sessionAuthenticator)($request))
        {
            $request->attributes->set(self::ATTR_ADMIN_USER, $user);
            return;
        }

        $event->setController(static fn (): Response => new JsonResponse(
            ['error' => 'Authentication required: Pimcore admin session or ' . ApiTokenRegistry::HEADER . ' header'],
            Response::HTTP_UNAUTHORIZED
        ));
    }
}
