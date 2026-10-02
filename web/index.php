<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$publicRoutes = [
    '/',
    '/login',
    '/join',
    '/domain',
    '/about-us',
    '/aboutUs',
    '/packages',
    '/support',
    '/support/terms',
    '/support/privacy',
    '/contact',
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
];
$middlewares = [
    new \CAMOO\Http\Middleware\AuthenticationMiddleware(
        static function (\Psr\Http\Message\ServerRequestInterface $request): ?array {
            $session = \CAMOO\Http\Session::create($request->getCookieParams());
            $user = (new \CAMOO\Http\SessionSegment(
                $session->segment(\CAMOO\Http\Session::SEG_NAME),
            ))->read('Auth.User');

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
            || $request->getAttribute('identity') !== null,
        new \Camoo\Http\Curl\Infrastructure\Response(
            body: new \Camoo\Http\Curl\Domain\Entity\Stream(''),
            statusCode: 403,
        ),
    ),
];
$caller = new \CAMOO\Http\Caller(dirname(__DIR__) . '/config', $middlewares);
$response = $caller->getResponse();
$statusCode = 200;
try {
    $statusCode = $response->getStatusCode();
} catch (\Throwable) {
}

http_response_code($statusCode);

try {
    foreach ($response->getHeaders() as $headerLines) {
        foreach ($headerLines as $name => $value) {
            foreach ((array)$value as $line) {
                header($name . ': ' . $line, false);
            }
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
