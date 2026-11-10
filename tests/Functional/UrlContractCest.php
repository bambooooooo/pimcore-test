<?php

namespace App\Tests\Functional;

use App\Security\ProtectedEndpoint;
use App\Tests\FunctionalTester;
use Codeception\Example;
use Symfony\Bundle\FrameworkBundle\Controller\RedirectController;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Enforces tests/Support/Data/url_contract.yaml - shared links must keep working.
 */
class UrlContractCest
{
    private const CONTRACT = __DIR__ . '/../Support/Data/url_contract.yaml';

    public function everyAppRouteIsListedInContract(FunctionalTester $I): void
    {
        $listed = array_keys(self::contract()['routes']);
        $unlisted = array_diff(array_keys($this->appRoutes($I)), $listed);

        $I->assertSame([], array_values($unlisted), 'Routes missing in url_contract.yaml - classify them (path, access, used_by)');
    }

    public function everyContractRouteStillExists(FunctionalTester $I): void
    {
        $missing = array_diff(array_keys(self::contract()['routes']), array_keys($this->allRoutes($I)));

        $I->assertSame([], array_values($missing), 'Routes from url_contract.yaml were removed or renamed - add a legacy redirect instead');
    }

    /**
     * @dataProvider contractRoutes
     */
    public function sampleUrlResolvesToRoute(FunctionalTester $I, Example $example): void
    {
        $router = $this->router($I);
        $context = $router->getContext();
        $previousMethod = $context->getMethod();
        $context->setMethod($example['method'] ?? 'GET');

        try
        {
            $matched = $router->match($example['path'])['_route'];
        }
        catch (RoutingException $e)
        {
            $matched = get_class($e);
        }
        finally
        {
            $context->setMethod($previousMethod);
        }

        $I->assertSame($example['route'], $matched, "{$example['path']} no longer resolves to {$example['route']}");
    }

    /**
     * @dataProvider contractRoutes
     */
    public function accessMatchesContract(FunctionalTester $I, Example $example): void
    {
        $route = $this->allRoutes($I)[$example['route']] ?? null;
        $I->assertNotNull($route, "Route {$example['route']} does not exist");

        $protected = $this->hasProtectedAttribute($route);

        match ($example['access'])
        {
            'protected' => $I->assertTrue($protected, "{$example['route']} must have #[ProtectedEndpoint]"),
            'public', 'login' => $I->assertFalse($protected, "{$example['route']} has #[ProtectedEndpoint] but is listed as {$example['access']}"),
            default => $I->fail("Unknown access [{$example['access']}] for {$example['route']}"),
        };

        if ($example['access'] === 'protected')
        {
            $this->send($I, $example);
            $I->seeResponseCodeIs(401);
        }

        if ($example['access'] === 'login')
        {
            $I->stopFollowingRedirects();
            $this->send($I, $example);
            $I->seeResponseCodeIsRedirection();
            $I->assertStringEndsWith('/factory/login', (string) $I->grabResponseHeader('Location'));
        }
    }

    public function validApiTokenOpensProtectedEndpoint(FunctionalTester $I): void
    {
        $I->haveValidApiToken();
        $I->amOnPage('/objects/stocks/999999999/5');

        // passes authentication and reaches the controller, which reports the missing object
        $I->seeResponseCodeIs(400);
        $I->see('Object not found');
    }

    public function invalidApiTokenIsRejected(FunctionalTester $I): void
    {
        $I->haveApiToken('not-a-valid-token');
        $I->amOnPage('/objects/stocks/999999999/5');

        $I->seeResponseCodeIs(401);
    }

    public function adminSessionOpensProtectedEndpoint(FunctionalTester $I): void
    {
        $I->amLoggedInAsPimcoreAdmin();
        $I->amOnPage('/objects/stocks/999999999/5');

        $I->seeResponseCodeIs(400);
    }

    public function everyLegacyRouteIsListedAsRedirect(FunctionalTester $I): void
    {
        $legacy = array_filter(
            $this->allRoutes($I),
            static fn (Route $route, string $name) => str_starts_with($name, 'legacy_'),
            ARRAY_FILTER_USE_BOTH
        );

        foreach ($legacy as $name => $route)
        {
            $I->assertStringStartsWith(RedirectController::class, (string) $route->getDefault('_controller'), "$name must use RedirectController");
            $I->assertTrue((bool) $route->getDefault('permanent'), "$name must be a permanent (301) redirect");
            $I->assertArrayHasKey((string) $route->getDefault('route'), $this->allRoutes($I), "$name points to a missing route");
        }

        $listedTargets = array_column(self::contract()['redirects'], 'to');
        $definedTargets = array_map(static fn (Route $route) => $route->getDefault('route'), $legacy);
        $I->assertSame([], array_values(array_diff($definedTargets, $listedTargets)), 'legacy_ routes missing under "redirects" in url_contract.yaml');
    }

    /**
     * @dataProvider contractRedirects
     */
    public function movedUrlRedirectsPermanently(FunctionalTester $I, Example $example): void
    {
        if (!empty($example['skip']))
        {
            return; // no URL has moved yet
        }

        $I->stopFollowingRedirects();
        $I->amOnPage($example['from'] . '?keep=1');

        $I->seeResponseCodeIs(301);
        $location = (string) $I->grabResponseHeader('Location');
        $target = parse_url($location, PHP_URL_PATH);

        $I->assertSame($example['to'], $this->router($I)->match($target)['_route'], "{$example['from']} redirects to $location");
        $I->assertStringContainsString('keep=1', $location, 'Query string must survive the redirect');
    }

    protected function contractRoutes(): array
    {
        $examples = [];
        foreach (self::contract()['routes'] as $name => $entry)
        {
            $examples[$name] = ['route' => $name] + $entry;
        }

        return $examples;
    }

    protected function contractRedirects(): array
    {
        return self::contract()['redirects'] ?: [['skip' => true, 'from' => '', 'to' => '']];
    }

    private static function contract(): array
    {
        static $contract;

        return $contract ??= Yaml::parseFile(self::CONTRACT);
    }

    private function send(FunctionalTester $I, Example $example): void
    {
        $method = $example['method'] ?? 'GET';
        $method === 'GET' ? $I->amOnPage($example['path']) : $I->sendAjaxRequest($method, $example['path']);
    }

    private function hasProtectedAttribute(Route $route): bool
    {
        $controller = (string) $route->getDefault('_controller');
        if (!str_contains($controller, '::'))
        {
            return false;
        }

        [$class, $method] = explode('::', $controller, 2);
        if (!method_exists($class, $method))
        {
            return false;
        }

        return (new \ReflectionMethod($class, $method))->getAttributes(ProtectedEndpoint::class)
            || (new \ReflectionClass($class))->getAttributes(ProtectedEndpoint::class);
    }

    /** @return array<string, Route> routes served by App\ controllers */
    private function appRoutes(FunctionalTester $I): array
    {
        return array_filter(
            $this->allRoutes($I),
            static fn (Route $route) => str_starts_with((string) $route->getDefault('_controller'), 'App\\')
        );
    }

    /** @return array<string, Route> */
    private function allRoutes(FunctionalTester $I): array
    {
        return $this->router($I)->getRouteCollection()->all();
    }

    private function router(FunctionalTester $I): RouterInterface
    {
        return $I->grabService('router');
    }
}
