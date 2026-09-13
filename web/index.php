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
        new \Camoo\Http\Curl\Infrastructure\Response(statusCode: 401),
        required: false,
    ),
    new \CAMOO\Http\Middleware\AuthorizationMiddleware(
        static fn (\Psr\Http\Message\ServerRequestInterface $request): bool =>
            in_array($request->getUri()->getPath(), $publicRoutes, true)
            || $request->getAttribute('identity') !== null,
        new \Camoo\Http\Curl\Infrastructure\Response(statusCode: 403),
    ),
];
$caller = new \CAMOO\Http\Caller(dirname(__DIR__) . '/config', $middlewares);
$response = $caller->getResponse();

// Rendered responses use Camoo's lightweight response object, whose optional
// header container is empty by default. Emit the body directly at this
// application boundary and use the normal successful status for page views.
http_response_code(200);
$body = $response->getBody();
if ($body->isSeekable()) {
    $body->rewind();
}
while (!$body->eof()) {
    echo $body->read(8192);
}
