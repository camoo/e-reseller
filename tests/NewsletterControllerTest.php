<?php

declare(strict_types=1);

namespace App\Test;

use App\Controller\AppController;
use App\Controller\NewsletterController;
use Aura\Session\CsrfToken;
use CAMOO\Exception\Http\MethodNotAllowedException;
use CAMOO\Http\ServerRequest;
use CAMOO\Http\Session;
use CAMOO\Http\SessionSegment;
use CAMOO\TestCase\ControllerTestCase;
use CAMOO\Utils\Configure;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest as GuzzleServerRequest;

final class NewsletterControllerTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        Configure::write('RESELLER_SITE', [
            'title_for_layout' => 'Test site',
            'domain' => 'showcase.example.test',
        ]);
        putenv('APP_ENV=development');
        putenv('HTTP_HOST=localhost');
    }

    public function testSubscribeRejectsGetMethod(): void
    {
        $controller = $this->createApplicationController(NewsletterController::class, 'subscribe', 'GET');

        $this->expectException(MethodNotAllowedException::class);
        $this->dispatchAction($controller);
    }

    public function testSubscribeRedirectsForNonAjaxWithInvalidEmail(): void
    {
        $controller = $this->createApplicationController(
            NewsletterController::class,
            'subscribe',
            'POST',
            '/newsletter/subscribe',
            ['Content-Type' => 'application/x-www-form-urlencoded'],
            '',
            ['email' => 'invalid-email'],
            false,
        );

        $response = $this->dispatchAction($controller);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/', $response->getHeaderLine('Location'));
    }

    public function testSubscribeReturnsJsonForAjaxWithInvalidEmail(): void
    {
        $controller = $this->createApplicationController(
            NewsletterController::class,
            'subscribe',
            'POST',
            '/newsletter/subscribe',
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'X-Requested-With' => 'XMLHttpRequest',
                'Accept' => 'application/json',
            ],
            '',
            ['email' => 'invalid-email'],
            true,
        );

        $response = $this->dispatchAction($controller);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));

        $data = json_decode((string)$response->getBody(), true);
        self::assertIsArray($data);
        self::assertFalse($data['status']);
        self::assertFalse($data['success']);
        self::assertNotEmpty($data['message']);
    }

    public function testSubscribeReturnsSuccessForAjaxWhenHoneypotTriggered(): void
    {
        $controller = $this->createApplicationController(
            NewsletterController::class,
            'subscribe',
            'POST',
            '/newsletter/subscribe',
            [
                'Content-Type' => 'application/x-www-form-urlencoded',
                'X-Requested-With' => 'XMLHttpRequest',
            ],
            '',
            ['email' => 'bot@example.com', 'website' => 'spam-bot'],
            true,
        );

        $response = $this->dispatchAction($controller);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));

        $data = json_decode((string)$response->getBody(), true);
        self::assertIsArray($data);
        self::assertTrue($data['status']);
        self::assertTrue($data['success']);
    }

    private function createApplicationController(
        string $controllerClass,
        string $action,
        string $method = 'GET',
        string $uri = '/',
        array $headers = [],
        string $body = '',
        array $parsedBody = [],
        bool $isAjax = true,
    ): AppController {
        $controller = $this->createController($controllerClass, $action, $method, $uri, $headers, $body);
        $controller->setResponse(new Response());

        $serverParams = [];
        if ($isAjax) {
            $serverParams['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
            $serverParams['HTTP_ACCEPT'] = 'application/json';
        }

        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $session = Session::create();
            $csrfToken = $session->getCsrfToken();
            $csrfToken->regenerateValue();
            $tokenValue = $csrfToken->getValue();
            $csrfSegment = new SessionSegment($session->segment(CsrfToken::class));
            $csrfSegment->write('__csrf_created_at', time());

            $parsedBody['__csrf_Token'] = $tokenValue;
            $guzzleRequest = (new GuzzleServerRequest($method, $uri, $headers, $body, '1.1', $serverParams))
                ->withParsedBody($parsedBody);
            $controller->request = new ServerRequest($guzzleRequest);
        } else {
            $guzzleRequest = new GuzzleServerRequest($method, $uri, $headers, $body, '1.1', $serverParams);
            $controller->request = new ServerRequest($guzzleRequest);
        }

        return $controller;
    }
}
