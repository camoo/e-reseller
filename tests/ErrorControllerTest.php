<?php

declare(strict_types=1);

namespace App\Test;

use App\Controller\ErrorController;
use CAMOO\TestCase\ControllerTestCase;
use CAMOO\Utils\Configure;

final class ErrorControllerTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        Configure::write('RESELLER_SITE', [
            'title_for_layout' => 'Acme Hosting',
            'site_desc_long' => 'Web hosting services',
            'tags' => 'hosting,web',
            'company_name' => 'Acme Inc',
        ]);
        Configure::write('HomeContent', []);
        putenv('APP_ENV=development');
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testRenderError404(): void
    {
        $controller = new ErrorController();
        $response = $controller->renderError(404);

        self::assertSame(404, $response->getStatusCode());
        self::assertStringContainsString('text/html', $response->getHeaderLine('Content-Type'));

        $body = (string)$response->getBody();
        self::assertStringContainsString('404', $body);
        self::assertStringContainsString('Page introuvable', $body);
    }

    public function testRenderError403(): void
    {
        $controller = new ErrorController();
        $response = $controller->renderError(403);

        self::assertSame(403, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('403', $body);
        self::assertStringContainsString('Accès interdit', $body);
    }

    public function testRenderError500(): void
    {
        $controller = new ErrorController();
        $response = $controller->renderError(500);

        self::assertSame(500, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('500', $body);
        self::assertStringContainsString('Erreur interne du serveur', $body);
    }

    public function testRenderErrorWithCustomMessage(): void
    {
        $controller = new ErrorController();
        $response = $controller->renderError(400, 'Custom bad request message');

        self::assertSame(400, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('400', $body);
        self::assertStringContainsString('Custom bad request message', $body);
    }
}
