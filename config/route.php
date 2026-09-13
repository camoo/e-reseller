<?php

declare(strict_types=1);

return [
    FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $routeCollector): void {
        $routeCollector->addRoute('GET', '/', ['controller' => 'Pages', 'action' => 'overview']);
        $routeCollector->addRoute(['POST', 'GET'], '/login', ['controller' => 'Users', 'action' => 'login']);
        $routeCollector->addRoute(['POST', 'GET'], '/join', ['controller' => 'Users', 'action' => 'join']);
        $routeCollector->addRoute('GET', '/logout', ['controller' => 'Users', 'action' => 'logout']);
        $routeCollector->addRoute('GET', '/sso', ['controller' => 'Users', 'action' => 'getSSO']);
        $routeCollector->addRoute('POST', '/domain-whois', ['controller' => 'Domains', 'action' => 'domainSearch']);
        $routeCollector->addRoute('POST', '/domain-add-to-basket', ['controller' => 'Domains', 'action' => 'addToBasket']);
        $routeCollector->addRoute('POST', '/domain-remove-basket', ['controller' => 'Domains', 'action' => 'removeFromBasket']);
        $routeCollector->addRoute(['POST', 'GET'], '/domain', ['controller' => 'Domains', 'action' => 'overview']);
        // ..
    }),
];
