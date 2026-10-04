<?php

declare(strict_types=1);

namespace App\Test;

use App\Controller\AboutUsController;
use App\Controller\ContactController;
use App\Controller\DomainsController;
use App\Controller\PackagesController;
use App\Controller\PagesController;
use App\Controller\SmsController;
use App\Controller\SupportController;
use App\Controller\UsersController;
use CAMOO\Event\EventInterface;
use CAMOO\TestCase\ControllerTestCase;
use CAMOO\Utils\Configure;
use Cake\Event\EventListenerInterface;
use GuzzleHttp\Psr7\Response;
use CAMOO\Exception\Http\MethodNotAllowedException;
use Psr\Http\Message\ResponseInterface;

final class ControllerResponseTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        Configure::write('RESELLER_SITE', [
            'title_for_layout' => 'Test site',
            'site_desc_long' => 'Test description',
            'tags' => 'test',
        ]);
        Configure::write('Features', ['domains' => true, 'hosting' => true]);
        putenv('APP_ENV=development');
        putenv('HTTP_HOST=localhost');
        putenv('HTTPS=off');
    }

    public function testPageControllersReturnRenderedResponses(): void
    {
        $controllers = [
            AboutUsController::class,
            ContactController::class,
            PackagesController::class,
            PagesController::class,
            SupportController::class,
        ];

        foreach ($controllers as $controllerClass) {
            $controller = $this->createApplicationController($controllerClass, 'overview');
            $controller->getEventManager()->on(new RenderResponseListener());
            $response = $this->dispatchAction($controller);

            self::assertInstanceOf(ResponseInterface::class, $response, $controllerClass);
            self::assertSame(200, $response->getStatusCode(), $controllerClass);
            self::assertNotSame('', (string)$response->getBody(), $controllerClass);
        }
    }

    public function testSupportPageActionsReturnRenderedResponses(): void
    {
        foreach (['terms', 'privacy'] as $action) {
            $controller = $this->createApplicationController(SupportController::class, $action);
            $controller->getEventManager()->on(new RenderResponseListener());
            $response = $this->dispatchAction($controller);

            self::assertSame(200, $response->getStatusCode());
            self::assertNotSame('', (string)$response->getBody());
        }
    }

    public function testSmsActionReturnsJsonResponse(): void
    {
        $controller = $this->createApplicationController(SmsController::class, 'balance');
        $response = $this->dispatchAction($controller);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        self::assertSame(['balance' => 100, 'currency' => 'XAF'], json_decode((string)$response->getBody(), true));
    }

    public function testLoginPageReturnsResponse(): void
    {
        $controller = $this->createApplicationController(UsersController::class, 'login', 'GET');
        $controller->getEventManager()->on(new RenderResponseListener());
        $response = $this->dispatchAction($controller);

        self::assertSame(200, $response->getStatusCode());
        self::assertNotSame('', (string)$response->getBody());
    }

    public function testRegisterPageReturnsResponse(): void
    {
        $controller = $this->createApplicationController(UsersController::class, 'register', 'GET');
        $controller->getEventManager()->on(new RenderResponseListener());
        $response = $this->dispatchAction($controller);

        self::assertSame(200, $response->getStatusCode());
        self::assertNotSame('', (string)$response->getBody());
    }

    public function testDomainOverviewRedirectReturnsResponseWhenDomainIsMissing(): void
    {
        $controller = $this->createApplicationController(DomainsController::class, 'overview');
        $response = $this->dispatchAction($controller);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/', $response->getHeaderLine('Location'));
    }

    public function testDomainOverviewRendersWithDomainQuery(): void
    {
        $controller = $this->createApplicationController(DomainsController::class, 'overview', 'GET', '/domain?d=testcamoo.cm');
        $controller->getEventManager()->on(new RenderResponseListener());
        $response = $this->dispatchAction($controller);

        self::assertSame(200, $response->getStatusCode());
        self::assertNotSame('', (string)$response->getBody());
    }

    public function testLogoutRejectsGetRequests(): void
    {
        $controller = $this->createApplicationController(UsersController::class, 'logout', 'GET');

        $this->expectException(MethodNotAllowedException::class);
        $this->dispatchAction($controller);
    }

    private function createApplicationController(
        string $controllerClass,
        string $action,
        string $method = 'GET',
        string $uri = '/',
    ): \CAMOO\Controller\AppController {
        $controller = $this->createController($controllerClass, $action, $method, $uri);
        $query = parse_url($uri, PHP_URL_QUERY);
        if (!empty($query)) {
            parse_str($query, $queryParams);
            $psr = (new \GuzzleHttp\Psr7\ServerRequest($method, $uri))->withQueryParams($queryParams);
            $controller->request = new \CAMOO\Http\ServerRequest($psr);
        }
        $controller->setResponse(new Response());

        return $controller;
    }
}

final class RenderResponseListener implements EventListenerInterface
{
    public function implementedEvents(): array
    {
        return ['AppController.beforeRender' => 'beforeRender'];
    }

    public function beforeRender(EventInterface $event): void
    {
        $event->setResult(new Response(200, [], 'rendered'));
    }
}
