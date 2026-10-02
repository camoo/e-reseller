<?php

declare(strict_types=1);

namespace App\Test;

use App\Controller\WellKnownController;
use CAMOO\TestCase\ControllerTestCase;
use CAMOO\Utils\Configure;
use FastRoute\Dispatcher\GroupCountBased;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;

final class WellKnownControllerTest extends ControllerTestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
        Configure::write('RESELLER_SITE', [
            'title_for_layout' => 'Acme Cloud Hosting',
            'site_desc_long' => 'Reliable and secure cloud hosting solutions.',
            'tags' => 'cloud,hosting,domains',
            'domain' => 'acme-hosting.test',
            'contact_email' => 'security@acme-hosting.test',
        ]);
        Configure::write('Features', ['domains' => true, 'hosting' => true]);
        Configure::write('HomeContent', []);
        Configure::write('WellKnown', [
            'resources' => [],
            'sitemap_urls' => [],
        ]);
        putenv('APP_ENV=development');
        putenv('HTTP_HOST=acme-hosting.test');
        putenv('HTTPS=on');
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        parent::tearDown();
    }

    public function testRobotsReturnsDefaultContent(): void
    {
        $wellKnownController = $this->createWellKnownController('robots');
        $response = $this->dispatchAction($wellKnownController);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/plain; charset=UTF-8', $response->getHeaderLine('Content-Type'));

        $body = (string)$response->getBody();
        self::assertStringContainsString('User-agent: *', $body);
        self::assertStringContainsString('Allow: /', $body);
        self::assertStringContainsString('Disallow: /admin', $body);
        self::assertStringContainsString('Disallow: /basket', $body);
        self::assertStringContainsString('Sitemap: https://acme-hosting.test/sitemap.xml', $body);
    }

    public function testRobotsAllowsCustomStringOverride(): void
    {
        Configure::write('WellKnown.robots', "User-agent: Googlebot\nDisallow: /nogoogle\n");

        $wellKnownController = $this->createWellKnownController('robots');
        $response = $this->dispatchAction($wellKnownController);

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertSame("User-agent: Googlebot\nDisallow: /nogoogle\n", $body);
    }

    public function testRobotsAllowsCustomArrayOverride(): void
    {
        Configure::write('WellKnown.robots', [
            'User-agent' => 'Bingbot',
            'Allow' => ['/public'],
            'Disallow' => ['/private', '/restricted'],
            'Sitemap' => 'https://acme-hosting.test/custom-sitemap.xml',
        ]);

        $wellKnownController = $this->createWellKnownController('robots');
        $response = $this->dispatchAction($wellKnownController);

        self::assertSame(200, $response->getStatusCode());
        $body = (string)$response->getBody();
        self::assertStringContainsString('User-agent: Bingbot', $body);
        self::assertStringContainsString('Allow: /public', $body);
        self::assertStringContainsString('Disallow: /private', $body);
        self::assertStringContainsString('Disallow: /restricted', $body);
        self::assertStringContainsString('Sitemap: https://acme-hosting.test/custom-sitemap.xml', $body);
    }

    public function testWellKnownContentCanBeOverriddenThroughHomeContent(): void
    {
        Configure::write('HomeContent', [
            'well_known' => [
                'robots' => "User-agent: *\nDisallow: /private-demo\n",
                'sitemap_urls' => "/campaign\nhttps://partner.test/landing\n",
            ],
        ]);

        $robotsResponse = $this->dispatchAction($this->createWellKnownController('robots'));
        self::assertSame("User-agent: *\nDisallow: /private-demo\n", (string)$robotsResponse->getBody());

        $sitemapResponse = $this->dispatchAction($this->createWellKnownController('sitemap'));
        $sitemapBody = (string)$sitemapResponse->getBody();
        self::assertStringContainsString('<loc>https://acme-hosting.test/campaign</loc>', $sitemapBody);
        self::assertStringContainsString('<loc>https://partner.test/landing</loc>', $sitemapBody);
    }

    public function testSitemapReturnsValidXmlWithFeatures(): void
    {
        $wellKnownController = $this->createWellKnownController('sitemap');
        $response = $this->dispatchAction($wellKnownController);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/xml; charset=UTF-8', $response->getHeaderLine('Content-Type'));
        self::assertSame('noindex', $response->getHeaderLine('X-Robots-Tag'));

        $body = (string)$response->getBody();
        self::assertStringContainsString('<?xml version="1.0" encoding="UTF-8"?>', $body);
        self::assertStringContainsString('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">', $body);
        self::assertStringContainsString('<loc>https://acme-hosting.test/</loc>', $body);
        self::assertStringContainsString('<loc>https://acme-hosting.test/domain</loc>', $body);
        self::assertStringContainsString('<loc>https://acme-hosting.test/packages</loc>', $body);
        self::assertStringContainsString('<loc>https://acme-hosting.test/about-us</loc>', $body);
        self::assertStringContainsString('<loc>https://acme-hosting.test/support/terms</loc>', $body);
        self::assertStringContainsString('<loc>https://acme-hosting.test/login</loc>', $body);
        self::assertStringContainsString('<loc>https://acme-hosting.test/register</loc>', $body);

        $xml = simplexml_load_string($body);
        self::assertNotFalse($xml, 'Sitemap should be valid XML');
    }

    public function testSitemapExcludesDisabledFeatures(): void
    {
        Configure::write('Features', ['domains' => false, 'hosting' => false]);

        $wellKnownController = $this->createWellKnownController('sitemap');
        $response = $this->dispatchAction($wellKnownController);

        $body = (string)$response->getBody();
        self::assertStringNotContainsString('<loc>https://acme-hosting.test/domain</loc>', $body);
        self::assertStringNotContainsString('<loc>https://acme-hosting.test/packages</loc>', $body);
        self::assertStringContainsString('<loc>https://acme-hosting.test/about-us</loc>', $body);
    }

    public function testSitemapAllowsCustomOverrideAndAdditionalUrls(): void
    {
        Configure::write('WellKnown.sitemap_urls', [
            '/promotions',
            'https://external.test/partner',
        ]);

        $wellKnownController = $this->createWellKnownController('sitemap');
        $response = $this->dispatchAction($wellKnownController);

        $body = (string)$response->getBody();
        self::assertStringContainsString('<loc>https://acme-hosting.test/promotions</loc>', $body);
        self::assertStringContainsString('<loc>https://external.test/partner</loc>', $body);
    }

    public function testLlmsReturnsMarkdownOverview(): void
    {
        $wellKnownController = $this->createWellKnownController('llms');
        $response = $this->dispatchAction($wellKnownController);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/markdown; charset=UTF-8', $response->getHeaderLine('Content-Type'));

        $body = (string)$response->getBody();
        self::assertStringContainsString('# Acme Cloud Hosting', $body);
        self::assertStringContainsString('> Reliable and secure cloud hosting solutions.', $body);
        self::assertStringContainsString('[Domain Registration](https://acme-hosting.test/domain)', $body);
        self::assertStringContainsString('[Web Hosting Packages](https://acme-hosting.test/packages)', $body);
        self::assertStringContainsString('[Terms of Service](https://acme-hosting.test/support/terms)', $body);
        self::assertStringContainsString('[XML Sitemap](https://acme-hosting.test/sitemap.xml)', $body);
    }

    public function testLlmsAllowsCustomOverride(): void
    {
        Configure::write('WellKnown.llms', "# Custom LLM\n\nCustom instruction for LLM bots.\n");

        $wellKnownController = $this->createWellKnownController('llms');
        $response = $this->dispatchAction($wellKnownController);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame("# Custom LLM\n\nCustom instruction for LLM bots.\n", (string)$response->getBody());
    }

    public function testSecurityTxtReturnsRfcCompliantContent(): void
    {
        $wellKnownController = $this->createWellKnownController('securityTxt');
        $response = $this->dispatchAction($wellKnownController);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/plain; charset=UTF-8', $response->getHeaderLine('Content-Type'));

        $body = (string)$response->getBody();
        self::assertStringContainsString('Contact: mailto:security@acme-hosting.test', $body);
        self::assertStringContainsString('Expires: ', $body);
        self::assertStringContainsString('Canonical: https://acme-hosting.test/.well-known/security.txt', $body);
    }

    public function testChangePasswordRedirects(): void
    {
        $wellKnownController = $this->createWellKnownController('changePassword');
        $response = $this->dispatchAction($wellKnownController);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/login', $response->getHeaderLine('Location'));
    }

    public function testChangePasswordRedirectsToCustomDestination(): void
    {
        Configure::write('WellKnown.change_password_redirect', '/profile/edit');

        $wellKnownController = $this->createWellKnownController('changePassword');
        $response = $this->dispatchAction($wellKnownController);

        self::assertSame(302, $response->getStatusCode());
        self::assertSame('/profile/edit', $response->getHeaderLine('Location'));
    }

    public function testResourceDelegatesToSecurityTxtAndChangePassword(): void
    {
        $wellKnownController = $this->createWellKnownController('resource', 'security.txt');
        $response = $this->dispatchAction($wellKnownController, 'resource', ['security.txt']);

        self::assertSame(200, $response->getStatusCode());
        self::assertStringContainsString('Contact: mailto:security@acme-hosting.test', (string)$response->getBody());

        $controller2 = $this->createWellKnownController('resource', 'change-password');
        $response2 = $this->dispatchAction($controller2, 'resource', ['change-password']);

        self::assertSame(302, $response2->getStatusCode());
    }

    public function testResourceReturnsConfiguredJsonPayload(): void
    {
        Configure::write('WellKnown.resources', [
            'assetlinks.json' => [
                [
                    'relation' => ['delegate_permission/common.handle_all_urls'],
                    'target' => ['namespace' => 'android_app', 'package_name' => 'com.acme.app'],
                ],
            ],
            'custom-token.txt' => 'verification-string-12345',
        ]);

        $wellKnownController = $this->createWellKnownController('resource', 'assetlinks.json');
        $response = $this->dispatchAction($wellKnownController, 'resource', ['assetlinks.json']);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('application/json; charset=UTF-8', $response->getHeaderLine('Content-Type'));
        $data = json_decode((string)$response->getBody(), true);
        self::assertSame('com.acme.app', $data[0]['target']['package_name']);

        $controllerText = $this->createWellKnownController('resource', 'custom-token.txt');
        $response = $this->dispatchAction($controllerText, 'resource', ['custom-token.txt']);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('text/plain; charset=UTF-8', $response->getHeaderLine('Content-Type'));
        self::assertSame('verification-string-12345', (string)$response->getBody());
    }

    public function testResourceRejectsPathTraversalAndUnknownFiles(): void
    {
        $wellKnownController = $this->createWellKnownController('resource', '../config/app.php');
        $response = $this->dispatchAction($wellKnownController, 'resource', ['../config/app.php']);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Not Found', (string)$response->getBody());

        $controllerUnknown = $this->createWellKnownController('resource', 'non-existent-resource.txt');
        $response = $this->dispatchAction($controllerUnknown, 'resource', ['non-existent-resource.txt']);

        self::assertSame(404, $response->getStatusCode());
        self::assertSame('Not Found', (string)$response->getBody());
    }

    public function testRouteConfigurationResolvesWellKnownRoutes(): void
    {
        $dispatcher = require __DIR__ . '/../config/route.php';
        /** @var GroupCountBased $routeDispatcher */
        $routeDispatcher = $dispatcher[0];

        $routesToTest = [
            '/robots.txt' => ['controller' => 'WellKnown', 'action' => 'robots'],
            '/sitemap.xml' => ['controller' => 'WellKnown', 'action' => 'sitemap'],
            '/llms.txt' => ['controller' => 'WellKnown', 'action' => 'llms'],
            '/llm.txt' => ['controller' => 'WellKnown', 'action' => 'llms'],
            '/.well-known/security.txt' => ['controller' => 'WellKnown', 'action' => 'securityTxt'],
            '/.well-known/change-password' => ['controller' => 'WellKnown', 'action' => 'changePassword'],
            '/.well-known/assetlinks.json' => ['controller' => 'WellKnown', 'action' => 'resource'],
        ];

        foreach ($routesToTest as $path => $expected) {
            $routeInfo = $routeDispatcher->dispatch('GET', $path);
            self::assertSame(\FastRoute\Dispatcher::FOUND, $routeInfo[0], 'Route should match: ' . $path);
            self::assertSame($expected['controller'], $routeInfo[1]['controller'], 'Controller mismatch for: ' . $path);
            self::assertSame($expected['action'], $routeInfo[1]['action'], 'Action mismatch for: ' . $path);
        }
    }

    private function createWellKnownController(string $action, ?string $arg = null): WellKnownController
    {
        $appController = $this->createController(WellKnownController::class, $action);
        $appController->setResponse(new Response());

        $path = match ($action) {
            'robots' => '/robots.txt',
            'sitemap' => '/sitemap.xml',
            'llms' => '/llms.txt',
            'securityTxt' => '/.well-known/security.txt',
            'changePassword' => '/.well-known/change-password',
            'resource' => '/.well-known/' . ($arg ?? ''),
            default => '/',
        };

        $serverParams = ['HTTP_HOST' => 'acme-hosting.test', 'HTTPS' => 'on'];
        $serverRequest = new ServerRequest('GET', 'https://acme-hosting.test' . $path, [], '', '1.1', $serverParams);
        $appController->request = new \CAMOO\Http\ServerRequest($serverRequest);

        return $appController;
    }
}
