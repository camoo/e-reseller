<?php
declare(strict_types=1);

if (PHP_SAPI === 'cli-server') {
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
    if ($path !== '/' && is_file(__DIR__ . $path)) {
        return false;
    }
}

require dirname(__DIR__) . '/vendor/autoload.php';

$publicRoutes = [
    '/',
    '/login',
    '/register',
    '/join',
    '/domain',
    '/about-us',
    '/aboutUs',
    '/packages',
    '/support',
    '/support/terms',
    '/support/privacy',
    '/contact',
    '/newsletter/subscribe',
    '/packages/vps',
    '/packages/email',
    '/packages/emails',
    '/packages/ssl',
    '/domain-whois',
    '/domain-add-to-basket',
    '/domain-remove-basket',
    '/basket',
    '/basket/add',
    '/basket/delete',
    '/basket/add-domain-to-hosting',
    '/domains/is-valid',
    '/domains/decision',
    '/domains/decision/',
    '/robots.txt',
    '/sitemap.xml',
    '/llms.txt',
    '/security.txt',
];
$middlewares = [
    new \CAMOO\Http\Middleware\AuthenticationMiddleware(
        static function (\Psr\Http\Message\ServerRequestInterface $request): ?array {
            $session = \CAMOO\Http\Session::create($request->getCookieParams());
            $user = new \CAMOO\Http\SessionSegment(
                $session->segment(\CAMOO\Http\Session::SEG_NAME),
            )->read('Auth.User');

            return is_array($user) && !empty($user['id']) ? $user : null;
        },
        new \Camoo\Http\Curl\Infrastructure\Response(
            body: new \Camoo\Http\Curl\Domain\Entity\Stream(''),
            statusCode: 401,
        ),
        required: false,
    ),
    new \CAMOO\Http\Middleware\AuthorizationMiddleware(
        static fn (\Psr\Http\Message\ServerRequestInterface $request): bool =>
            in_array($request->getUri()->getPath(), $publicRoutes, true)
            || str_starts_with($request->getUri()->getPath(), '/.well-known/')
            || $request->getAttribute('identity') !== null,
        new \Camoo\Http\Curl\Infrastructure\Response(
            body: new \Camoo\Http\Curl\Domain\Entity\Stream(''),
            statusCode: 403,
        ),
    ),
];

try {
    $caller = new \CAMOO\Http\Caller(dirname(__DIR__) . '/config', $middlewares);
    $response = $caller->getResponse();
} catch (\Throwable $exception) {
    if (\CAMOO\Utils\Configure::read('debug')) {
        throw $exception;
    }
    \App\Lib\ErrorLogger::log($exception);
    $code = ($exception->getCode() >= 400 && $exception->getCode() < 600) ? (int)$exception->getCode() : 500;
    $response = new \App\Controller\ErrorController()->renderError($code, $exception->getMessage());
}

$statusCode = 200;
try {
    $statusCode = $response->getStatusCode();
} catch (\Throwable) {
}

if ($statusCode >= 400 && !\App\Lib\ErrorLogger::hasLogged()) {
    \App\Lib\ErrorLogger::logHttpError($statusCode);
}

if ($statusCode >= 400) {
    $isAjax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower((string)$_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && str_contains(strtolower((string)$_SERVER['HTTP_ACCEPT']), 'application/json'));

    $bodySize = 0;
    try {
        $bodySize = $response->getBody()->getSize() ?? 0;
    } catch (\Throwable) {
    }

    if ($bodySize === 0) {
        if ($isAjax) {
            $response = new \GuzzleHttp\Psr7\Response(
                $statusCode,
                ['Content-Type' => 'application/json'],
                json_encode([
                    'status' => false,
                    'code' => $statusCode,
                    'message' => $statusCode === 404 ? 'Not Found' : ($statusCode === 403 ? 'Forbidden' : 'Error'),
                ], JSON_THROW_ON_ERROR)
            );
        } else {
            $response = new \App\Controller\ErrorController()->renderError($statusCode);
        }
    }
}

http_response_code($statusCode);

try {
    foreach ($response->getHeaders() as $name => $values) {
        foreach ((array)$values as $line) {
            header($name . ': ' . $line, false);
        }
    }
} catch (\Throwable) {
}

$body = $response->getBody();
if ($body->isSeekable()) {
    $body->rewind();
}
while (!$body->eof()) {
    echo $body->read(8192);
}
