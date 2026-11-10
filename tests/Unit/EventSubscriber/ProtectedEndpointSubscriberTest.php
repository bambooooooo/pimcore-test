<?php

namespace App\Tests\Unit\EventSubscriber;

use App\EventSubscriber\ProtectedEndpointSubscriber;
use App\Security\ApiTokenRegistry;
use App\Security\ProtectedEndpoint;
use Codeception\Test\Unit;
use Pimcore\Model\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ProtectedEndpointSubscriberTest extends Unit
{
    private const TOKEN = 'valid-token';

    public function testUnprotectedControllerIsUntouched(): void
    {
        $event = $this->event([new UnprotectedController(), 'action'], new Request());

        $this->subscriber(null)->onController($event);

        $this->assertSame('ok', ($event->getController())()->getContent());
    }

    public function testProtectedControllerRejectsAnonymousRequest(): void
    {
        $event = $this->event([new ProtectedController(), 'action'], new Request());

        $this->subscriber(null)->onController($event);

        $response = ($event->getController())();
        $this->assertSame(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
        $this->assertStringContainsString('X-Api-Token', $response->getContent());
    }

    public function testClassLevelAttributeProtectsEveryAction(): void
    {
        $event = $this->event([new ProtectedClassController(), 'action'], new Request());

        $this->subscriber(null)->onController($event);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, ($event->getController())()->getStatusCode());
    }

    public function testValidApiTokenPassesAndIdentifiesClient(): void
    {
        $request = new Request();
        $request->headers->set(ApiTokenRegistry::HEADER, self::TOKEN);
        $event = $this->event([new ProtectedController(), 'action'], $request);

        $this->subscriber(null)->onController($event);

        $this->assertSame('ok', ($event->getController())()->getContent());
        $this->assertSame('erp', $request->attributes->get(ProtectedEndpointSubscriber::ATTR_API_CLIENT));
    }

    public function testInvalidApiTokenWithoutSessionIsRejected(): void
    {
        $request = new Request();
        $request->headers->set(ApiTokenRegistry::HEADER, 'wrong');
        $event = $this->event([new ProtectedController(), 'action'], $request);

        $this->subscriber(null)->onController($event);

        $this->assertSame(Response::HTTP_UNAUTHORIZED, ($event->getController())()->getStatusCode());
    }

    public function testAdminSessionPasses(): void
    {
        $user = new User();
        $request = new Request();
        $event = $this->event([new ProtectedController(), 'action'], $request);

        $this->subscriber($user)->onController($event);

        $this->assertSame('ok', ($event->getController())()->getContent());
        $this->assertSame($user, $request->attributes->get(ProtectedEndpointSubscriber::ATTR_ADMIN_USER));
    }

    private function subscriber(?User $sessionUser): ProtectedEndpointSubscriber
    {
        return new ProtectedEndpointSubscriber(new ApiTokenRegistry('erp:' . self::TOKEN), static fn () => $sessionUser);
    }

    private function event(callable $controller, Request $request): ControllerEvent
    {
        return new ControllerEvent($this->createMock(HttpKernelInterface::class), $controller, $request, HttpKernelInterface::MAIN_REQUEST);
    }
}

class UnprotectedController
{
    public function action(): Response
    {
        return new Response('ok');
    }
}

class ProtectedController
{
    #[ProtectedEndpoint]
    public function action(): Response
    {
        return new Response('ok');
    }
}

#[ProtectedEndpoint]
class ProtectedClassController
{
    public function action(): Response
    {
        return new Response('ok');
    }
}
