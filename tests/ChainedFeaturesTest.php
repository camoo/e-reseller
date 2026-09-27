<?php

declare(strict_types=1);

namespace App\Test;

use App\Controller\DomainsController;
use App\Controller\UsersController;
use App\Model\Rest\ContactsRest;
use App\Model\Rest\DomainsRest;
use App\Model\Rest\UsersRest;
use CAMOO\Controller\AppController;
use CAMOO\Exception\Exception;
use CAMOO\TestCase\ControllerTestCase;
use CAMOO\Utils\Configure;
use GuzzleHttp\Psr7\Response;

final class ChainedFeaturesTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        Configure::write('RESELLER_SITE', [
            'title_for_layout' => 'Test site',
            'site_desc_long' => 'Test description',
            'tags' => 'test',
        ]);
        Configure::write('Features', ['domains' => true, 'hosting' => true]);
        putenv('APP_ENV=development');
        putenv('HTTP_HOST=localhost');
        putenv('HTTPS=off');
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        unset($_SERVER['HTTP_X_REQUESTED_WITH']);
        parent::tearDown();
    }

    public function testGetBalanceThrowsWhenUserNotLoggedIn(): void
    {
        $controller = $this->createApplicationController(UsersController::class, 'getBalance', 'GET');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not loggedIn !');
        $this->dispatchAction($controller);
    }

    public function testGetBalanceReturnsJsonResponseWhenAuthenticated(): void
    {
        $controller = $this->createApplicationController(UsersController::class, 'getBalance', 'GET', '/balance');
        $controller->request->getSession()->write('loggedin', true);
        $controller->request->getSession()->write('Auth.User', ['id' => 42, 'name' => 'Test User']);

        $mockRest = $this->createMock(UsersRest::class);
        $mockRest->expects(self::once())->method('newRequest')->willReturnSelf();
        $mockRest->expects(self::once())->method('send')->willReturn([
            'customer_id' => 42,
            'username' => 'testuser',
            'balance' => 50000.0,
            'currency' => 'XAF',
            'unpaid_amount' => 0.0,
        ]);
        $controller->UsersRest = $mockRest;

        $response = $this->dispatchAction($controller);
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json', $response->getHeaderLine('Content-Type'));

        $body = json_decode((string)$response->getBody(), true);
        self::assertTrue($body['status']);
        self::assertEquals(50000.0, $body['balance']['balance']);
        self::assertSame('XAF', $body['balance']['currency']);
    }

    public function testEditProfileThrowsWhenUserNotLoggedIn(): void
    {
        $controller = $this->createApplicationController(UsersController::class, 'editProfile', 'POST');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User not loggedIn !');
        $this->dispatchAction($controller);
    }

    public function testEditProfileReturnsSuccessOnValidData(): void
    {
        $controller = $this->createApplicationController(UsersController::class, 'editProfile', 'POST', '/profile/edit');
        $controller->request->getSession()->write('loggedin', true);
        $controller->request->getSession()->write('Auth.User', ['id' => 42]);

        $mockRest = $this->createMock(UsersRest::class);
        $mockRest->expects(self::once())->method('newRequest')->willReturnSelf();
        $mockRest->expects(self::once())->method('getErrors')->willReturn([]);
        $mockRest->expects(self::once())->method('send')->willReturn(['status' => 'OK', 'message' => 'Profile updated']);
        $controller->UsersRest = $mockRest;

        $response = $this->dispatchAction($controller);
        self::assertSame(200, $response->getStatusCode());

        $body = json_decode((string)$response->getBody(), true);
        self::assertTrue($body['status']);
    }

    public function testEditContactValidatesAndCallsRest(): void
    {
        $controller = $this->createApplicationController(DomainsController::class, 'editContact', 'POST', '/contacts/edit');

        $mockContactsRest = $this->createMock(ContactsRest::class);
        $mockContactsRest->expects(self::once())->method('newRequest')->willReturnSelf();
        $mockContactsRest->expects(self::once())->method('getErrors')->willReturn([]);
        $mockContactsRest->expects(self::once())->method('send')->willReturn(['status' => 'OK', 'message' => 'Contact modified successfully']);

        $controller->ContactsRest = $mockContactsRest;

        $response = $this->dispatchAction($controller);
        self::assertSame(200, $response->getStatusCode());

        $body = json_decode((string)$response->getBody(), true);
        self::assertTrue($body['status']);
    }

    public function testResendVerificationValidatesAndCallsRest(): void
    {
        $controller = $this->createApplicationController(DomainsController::class, 'resendVerification', 'POST', '/domains/resend-verification');

        $mockDomainsRest = $this->createMock(DomainsRest::class);
        $mockDomainsRest->expects(self::once())->method('newRequest')->willReturnSelf();
        $mockDomainsRest->expects(self::once())->method('getErrors')->willReturn([]);
        $mockDomainsRest->expects(self::once())->method('send')->willReturn(['status' => 'OK', 'message' => 'Verification email resent successfully']);

        $controller->DomainsRest = $mockDomainsRest;

        $response = $this->dispatchAction($controller);
        self::assertSame(200, $response->getStatusCode());

        $body = json_decode((string)$response->getBody(), true);
        self::assertTrue($body['status']);
        self::assertSame('Email de vérification renvoyé avec succès', $body['message']);
    }

    private function createApplicationController(
        string $controllerClass,
        string $action,
        string $method = 'GET',
        string $uri = '/',
        array $headers = [],
        string $body = '',
        array $parsedBody = [],
    ): AppController {
        $controller = $this->createController($controllerClass, $action, $method, $uri, $headers, $body);
        $controller->setResponse(new Response());

        $serverParams = ['HTTP_X_REQUESTED_WITH' => 'XMLHttpRequest'];

        if (in_array(strtoupper($method), ['POST', 'PUT', 'DELETE', 'PATCH'], true)) {
            $session = \CAMOO\Http\Session::create();
            $csrfToken = $session->getCsrfToken();
            $csrfToken->regenerateValue();
            $tokenValue = $csrfToken->getValue();
            $csrfSegment = new \CAMOO\Http\SessionSegment($session->segment(\Aura\Session\CsrfToken::class));
            $csrfSegment->write('__csrf_created_at', time());

            $parsedBody['__csrf_Token'] = $tokenValue;
            $guzzleRequest = (new \GuzzleHttp\Psr7\ServerRequest($method, $uri, $headers, $body, '1.1', $serverParams))->withParsedBody($parsedBody);
            $controller->request = new \CAMOO\Http\ServerRequest($guzzleRequest);
        } else {
            $guzzleRequest = new \GuzzleHttp\Psr7\ServerRequest($method, $uri, $headers, $body, '1.1', $serverParams);
            $controller->request = new \CAMOO\Http\ServerRequest($guzzleRequest);
        }

        return $controller;
    }
}
