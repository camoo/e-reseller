<?php

declare(strict_types=1);

namespace App\Controller;

use CAMOO\Utils\Configure;
use Camoo\Http\Curl\Domain\Entity\Stream;
use Camoo\Http\Curl\Infrastructure\Response;
use Psr\Http\Message\ResponseInterface;

class WellKnownController extends AppController
{
    public function robots(): ResponseInterface
    {
        $custom = $this->getWellKnownOverride('robots');
        if (is_string($custom) && trim($custom) !== '') {
            return $this->textResponse($custom);
        }

        if (is_array($custom)) {
            return $this->textResponse($this->formatRobotsArray($custom));
        }

        $filePath = $this->findLocalOverrideFile('robots.txt');
        if ($filePath !== null) {
            return $this->textResponse((string)file_get_contents($filePath));
        }

        return $this->textResponse($this->getDefaultRobotsContent());
    }

    public function sitemap(): ResponseInterface
    {
        $custom = $this->getWellKnownOverride('sitemap');
        if (is_string($custom) && trim($custom) !== '') {
            return $this->textResponse($custom, 'application/xml; charset=UTF-8', 200, [
                'X-Robots-Tag' => 'noindex',
            ]);
        }

        $filePath = $this->findLocalOverrideFile('sitemap.xml');
        if ($filePath !== null) {
            return $this->textResponse((string)file_get_contents($filePath), 'application/xml; charset=UTF-8', 200, [
                'X-Robots-Tag' => 'noindex',
            ]);
        }

        return $this->textResponse($this->getDefaultSitemapXml(), 'application/xml; charset=UTF-8', 200, [
            'X-Robots-Tag' => 'noindex',
        ]);
    }

    public function llms(): ResponseInterface
    {
        $custom = $this->getWellKnownOverride('llms');
        if ($custom === null) {
            $custom = Configure::read('WellKnown.llms_content');
        }
        if (is_string($custom) && trim($custom) !== '') {
            return $this->textResponse($custom, 'text/markdown; charset=UTF-8');
        }

        $filePath = $this->findLocalOverrideFile('llms.txt');
        if ($filePath !== null) {
            return $this->textResponse((string)file_get_contents($filePath), 'text/markdown; charset=UTF-8');
        }

        return $this->textResponse($this->getDefaultLlmsContent(), 'text/markdown; charset=UTF-8');
    }

    public function securityTxt(): ResponseInterface
    {
        $custom = $this->getWellKnownOverride('security_txt');
        if (is_string($custom) && trim($custom) !== '') {
            return $this->textResponse($custom);
        }

        $filePath = $this->findLocalWellKnownFile('security.txt');
        if ($filePath !== null) {
            return $this->textResponse((string)file_get_contents($filePath));
        }

        return $this->textResponse($this->getDefaultSecurityTxt());
    }

    public function changePassword(): ResponseInterface
    {
        $destination = $this->getWellKnownOverride('change_password_redirect');
        if (!is_string($destination) || trim($destination) === '') {
            $isLoggedIn = $this->request?->getSession()?->check('loggedin')
                && $this->request->getSession()->read('loggedin') === true;
            $destination = $isLoggedIn ? '/profile/edit' : '/login';
        }

        return $this->redirect($destination);
    }

    public function resource(string $resource = ''): ResponseInterface
    {
        if ($resource === '') {
            $target = $this->request?->getRequestTarget() ?? '';
            $path = parse_url($target, PHP_URL_PATH) ?? '';
            if (str_starts_with($path, '/.well-known/')) {
                $resource = substr($path, strlen('/.well-known/'));
            }
        }

        $resource = trim(str_replace('\\', '/', $resource), '/');

        if ($resource === '' || str_contains($resource, '..')) {
            return $this->textResponse('Not Found', 'text/plain; charset=UTF-8', 404);
        }

        if ($resource === 'security.txt') {
            return $this->securityTxt();
        }

        if ($resource === 'change-password') {
            return $this->changePassword();
        }

        if ($resource === 'robots.txt') {
            return $this->robots();
        }

        if ($resource === 'sitemap.xml') {
            return $this->sitemap();
        }

        if ($resource === 'llms.txt' || $resource === 'llm.txt') {
            return $this->llms();
        }

        $configuredResources = $this->getWellKnownOverride('resources');
        if (is_array($configuredResources) && array_key_exists($resource, $configuredResources)) {
            $val = $configuredResources[$resource];
            if (is_array($val)) {
                return $this->textResponse(
                    (string)json_encode($val, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES),
                    'application/json; charset=UTF-8',
                );
            }

            $contentType = str_ends_with($resource, '.json')
                ? 'application/json; charset=UTF-8'
                : 'text/plain; charset=UTF-8';

            return $this->textResponse((string)$val, $contentType);
        }

        $filePath = $this->findLocalWellKnownFile($resource);
        if ($filePath !== null) {
            $contentType = match (true) {
                str_ends_with($resource, '.json') => 'application/json; charset=UTF-8',
                str_ends_with($resource, '.xml') => 'application/xml; charset=UTF-8',
                str_ends_with($resource, '.md') => 'text/markdown; charset=UTF-8',
                default => 'text/plain; charset=UTF-8',
            };

            return $this->textResponse((string)file_get_contents($filePath), $contentType);
        }

        return $this->textResponse('Not Found', 'text/plain; charset=UTF-8', 404);
    }

    protected function getBaseUrl(): string
    {
        $host = (string)($this->request?->getEnv('HTTP_HOST')
            ?: Configure::read('RESELLER_SITE.domain')
            ?: getenv('HTTP_HOST')
            ?: 'localhost');

        if (str_contains($host, ':')) {
            [$host] = explode(':', $host, 2);
        }

        $https = (string)($this->request?->getEnv('HTTPS') ?: getenv('HTTPS') ?: '');
        $forwardedProto = (string)($this->request?->getEnv('HTTP_X_FORWARDED_PROTO') ?: '');
        $requestScheme = (string)($this->request?->getEnv('REQUEST_SCHEME') ?: '');

        if ($forwardedProto === 'https' || (!empty($https) && $https !== 'off') || $requestScheme === 'https') {
            $scheme = 'https';
        } else {
            $scheme = 'http';
        }

        $port = $this->request?->getEnv('SERVER_PORT');
        $baseUrl = $scheme . '://' . $host;
        if (!empty($port) && is_numeric($port) && !in_array((int)$port, [80, 443], true)) {
            $baseUrl .= ':' . (int)$port;
        }

        return rtrim($baseUrl, '/');
    }

    /**
     * Read reseller-managed well-known content first, then preserve the
     * legacy Configure::write('WellKnown.*', ...) override for deployments
     * that still configure these endpoints in PHP.
     */
    protected function getWellKnownOverride(string $key): mixed
    {
        $showcaseValue = Configure::read('HomeContent.well_known.' . $key);
        if ((is_string($showcaseValue) && trim($showcaseValue) !== '')
            || (is_array($showcaseValue) && $showcaseValue !== [])) {
            return $showcaseValue;
        }

        return Configure::read('WellKnown.' . $key);
    }

    protected function getSiteTitle(): string
    {
        $title = Configure::read('RESELLER_SITE.title_for_layout');

        return is_string($title) && trim($title) !== '' ? trim($title) : 'Web Services';
    }

    protected function getSiteDescription(): string
    {
        $desc = Configure::read('RESELLER_SITE.site_desc_long');

        return is_string($desc) && trim($desc) !== ''
            ? trim($desc)
            : 'Domain registration and web hosting services.';
    }

    protected function getContactEmail(): string
    {
        $email = Configure::read('RESELLER_SITE.contact_email');
        if (is_string($email) && trim($email) !== '') {
            return trim($email);
        }

        $domain = Configure::read('RESELLER_SITE.domain');
        $host = is_string($domain) && trim($domain) !== '' ? trim($domain) : 'localhost';

        return 'security@' . $host;
    }

    protected function getDefaultRobotsContent(): string
    {
        $sitemapUrl = $this->getBaseUrl() . '/sitemap.xml';

        return implode("\n", [
            'User-agent: *',
            'Allow: /',
            'Disallow: /admin',
            'Disallow: /basket',
            'Disallow: /profile',
            'Disallow: /orders',
            'Disallow: /payments',
            '',
            'Sitemap: ' . $sitemapUrl,
            '',
        ]);
    }

    protected function getDefaultSitemapXml(): string
    {
        $baseUrl = $this->getBaseUrl();

        $urls = [
            ['loc' => $baseUrl . '/', 'priority' => 1.0, 'changefreq' => 'daily'],
        ];

        if ($this->isFeatureEnabled('domains')) {
            $urls[] = ['loc' => $baseUrl . '/domain', 'priority' => 0.9, 'changefreq' => 'weekly'];
        }

        if ($this->isFeatureEnabled('hosting')) {
            $urls[] = ['loc' => $baseUrl . '/packages', 'priority' => 0.9, 'changefreq' => 'weekly'];
        }

        $urls[] = ['loc' => $baseUrl . '/about-us', 'priority' => 0.8, 'changefreq' => 'monthly'];
        $urls[] = ['loc' => $baseUrl . '/contact', 'priority' => 0.8, 'changefreq' => 'monthly'];
        $urls[] = ['loc' => $baseUrl . '/support', 'priority' => 0.8, 'changefreq' => 'monthly'];
        $urls[] = ['loc' => $baseUrl . '/support/terms', 'priority' => 0.5, 'changefreq' => 'yearly'];
        $urls[] = ['loc' => $baseUrl . '/support/privacy', 'priority' => 0.5, 'changefreq' => 'yearly'];
        $urls[] = ['loc' => $baseUrl . '/login', 'priority' => 0.4, 'changefreq' => 'monthly'];
        $urls[] = ['loc' => $baseUrl . '/register', 'priority' => 0.6, 'changefreq' => 'monthly'];

        $extraUrls = $this->getWellKnownOverride('sitemap_urls');
        if (is_string($extraUrls)) {
            $extraUrls = preg_split('/\R+/', $extraUrls, -1, PREG_SPLIT_NO_EMPTY);
        }
        if (is_array($extraUrls)) {
            foreach ($extraUrls as $extraUrl) {
                if (is_string($extraUrl) && trim($extraUrl) !== '') {
                    $urls[] = [
                        'loc' => str_starts_with($extraUrl, 'http') ? $extraUrl : $baseUrl . '/' . ltrim($extraUrl, '/'),
                        'priority' => 0.7,
                        'changefreq' => 'monthly',
                    ];
                } elseif (is_array($extraUrl) && !empty($extraUrl['loc'])) {
                    $urls[] = $extraUrl;
                }
            }
        }

        $xmlWriter = new \XMLWriter();
        $xmlWriter->openMemory();
        $xmlWriter->setIndent(true);
        $xmlWriter->setIndentString('  ');
        $xmlWriter->startDocument('1.0', 'UTF-8');
        $xmlWriter->startElementNS(null, 'urlset', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        foreach ($urls as $url) {
            $xmlWriter->startElement('url');
            $xmlWriter->writeElement('loc', (string)$url['loc']);
            if (!empty($url['lastmod'])) {
                $xmlWriter->writeElement('lastmod', (string)$url['lastmod']);
            }

            if (!empty($url['changefreq'])) {
                $xmlWriter->writeElement('changefreq', (string)$url['changefreq']);
            }

            if (isset($url['priority'])) {
                $xmlWriter->writeElement('priority', number_format((float)$url['priority'], 1, '.', ''));
            }

            $xmlWriter->endElement();
        }

        $xmlWriter->endElement();
        $xmlWriter->endDocument();

        return $xmlWriter->outputMemory();
    }

    protected function getDefaultLlmsContent(): string
    {
        $baseUrl = $this->getBaseUrl();
        $title = $this->getSiteTitle();
        $description = $this->getSiteDescription();

        $lines = [
            '# ' . $title,
            '',
            '> ' . $description,
            '',
            '## Services',
        ];

        if ($this->isFeatureEnabled('domains')) {
            $lines[] = sprintf('- [Domain Registration](%s/domain): Search, register, and transfer domain names.', $baseUrl);
        }

        if ($this->isFeatureEnabled('hosting')) {
            $lines[] = sprintf('- [Web Hosting Packages](%s/packages): Web hosting plans and cloud server solutions.', $baseUrl);
        }

        $lines[] = sprintf('- [About Us](%s/about-us): Company background, mission, and overview.', $baseUrl);
        $lines[] = sprintf('- [Contact](%s/contact): Support and sales contact information.', $baseUrl);
        $lines[] = sprintf('- [Support](%s/support): Knowledge base and customer assistance.', $baseUrl);
        $lines[] = '';
        $lines[] = '## Documentation & Policies';
        $lines[] = sprintf('- [Terms of Service](%s/support/terms): Service terms, usage policies, and obligations.', $baseUrl);
        $lines[] = sprintf('- [Privacy Policy](%s/support/privacy): Data protection and privacy practices.', $baseUrl);
        $lines[] = '';
        $lines[] = '## Discoverability';
        $lines[] = sprintf('- [XML Sitemap](%s/sitemap.xml): Full list of public pages for crawlers and indexers.', $baseUrl);
        $lines[] = sprintf('- [Security Contact](%s/.well-known/security.txt): Responsible disclosure and security reporting.', $baseUrl);
        $lines[] = '';

        return implode("\n", $lines);
    }

    protected function getDefaultSecurityTxt(): string
    {
        $baseUrl = $this->getBaseUrl();
        $contact = $this->getContactEmail();
        $expires = gmdate('Y-m-d\TH:i:s\Z', strtotime('+1 year'));

        $lines = [
            'Contact: mailto:' . $contact,
            'Expires: ' . $expires,
            'Preferred-Languages: fr, en',
            'Canonical: ' . $baseUrl . '/.well-known/security.txt',
            '',
        ];

        return implode("\n", $lines);
    }

    protected function formatRobotsArray(array $config): string
    {
        $lines = [];
        $userAgent = $config['User-agent'] ?? $config['user_agent'] ?? '*';
        $lines[] = 'User-agent: ' . $userAgent;

        $allows = (array)($config['Allow'] ?? $config['allow'] ?? []);
        foreach ($allows as $allow) {
            $lines[] = 'Allow: ' . $allow;
        }

        $disallows = (array)($config['Disallow'] ?? $config['disallow'] ?? []);
        foreach ($disallows as $disallow) {
            $lines[] = 'Disallow: ' . $disallow;
        }

        $sitemap = $config['Sitemap'] ?? $config['sitemap'] ?? ($this->getBaseUrl() . '/sitemap.xml');
        if (!empty($sitemap)) {
            $lines[] = '';
            $lines[] = 'Sitemap: ' . $sitemap;
        }

        $lines[] = '';

        return implode("\n", $lines);
    }

    protected function textResponse(
        string $content,
        string $contentType = 'text/plain; charset=UTF-8',
        int $status = 200,
        array $headers = [],
    ): ResponseInterface {
        $response = $this->response ?? new Response();
        $stream = new Stream($content);

        $response = $response
            ->withStatus($status)
            ->withHeader('Content-Type', $contentType)
            ->withBody($stream);

        foreach ($headers as $header => $value) {
            $response = $response->withHeader($header, $value);
        }

        return $this->response = $response;
    }

    private function findLocalOverrideFile(string $filename): ?string
    {
        $candidates = [
            (defined('CONFIG') ? CONFIG : dirname(__DIR__, 2) . '/config/') . $filename,
            (defined('WEB') ? WEB : dirname(__DIR__, 2) . '/web/') . $filename,
        ];

        return array_find($candidates, fn($candidate) => is_file($candidate) && is_readable($candidate));

    }

    private function findLocalWellKnownFile(string $resource): ?string
    {
        $baseDirs = [
            defined('CONFIG') ? CONFIG . 'well-known' : dirname(__DIR__, 2) . '/config/well-known',
            defined('WEB') ? WEB . '.well-known' : dirname(__DIR__, 2) . '/web/.well-known',
        ];

        foreach ($baseDirs as $baseDir) {
            $realDir = realpath($baseDir);
            if ($realDir === false) {
                continue;
            }

            $targetFile = $realDir . DIRECTORY_SEPARATOR . $resource;
            $realTarget = realpath($targetFile);
            if ($realTarget !== false && str_starts_with($realTarget, $realDir) && is_file($realTarget)) {
                return $realTarget;
            }
        }

        return null;
    }
}
