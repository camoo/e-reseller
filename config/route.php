<?php

declare(strict_types=1);

return [
    FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $routeCollector): void {
        $routeCollector->addRoute('GET', '/', ['controller' => 'Pages', 'action' => 'overview']);
        $routeCollector->addRoute(['POST', 'GET'], '/login', ['controller' => 'Users', 'action' => 'login']);
        $routeCollector->addRoute(['POST', 'GET'], '/join', ['controller' => 'Users', 'action' => 'join']);
        $routeCollector->addRoute('POST', '/logout', ['controller' => 'Users', 'action' => 'logout']);
        $routeCollector->addRoute('GET', '/sso', ['controller' => 'Users', 'action' => 'getSSO']);
        $routeCollector->addRoute('POST', '/domain-whois', ['controller' => 'Domains', 'action' => 'domainSearch']);
        $routeCollector->addRoute('POST', '/domain-add-to-basket', ['controller' => 'Domains', 'action' => 'addToBasket']);
        $routeCollector->addRoute('POST', '/domain-remove-basket', ['controller' => 'Domains', 'action' => 'removeFromBasket']);
        $routeCollector->addRoute(['POST', 'GET'], '/domain', ['controller' => 'Domains', 'action' => 'overview']);
        $routeCollector->addRoute('GET', '/basket', ['controller' => 'Basket', 'action' => 'overview']);
        $routeCollector->addRoute('POST', '/basket/add', ['controller' => 'Basket', 'action' => 'add']);
        $routeCollector->addRoute('POST', '/basket/delete', ['controller' => 'Basket', 'action' => 'delete']);
        $routeCollector->addRoute('POST', '/basket/add-domain-to-hosting', ['controller' => 'Basket', 'action' => 'addDomainToHosting']);
        $routeCollector->addRoute('POST', '/domains/is-valid', ['controller' => 'Domains', 'action' => 'isValid']);
        $routeCollector->addRoute('GET', '/domains/decision', ['controller' => 'Domains', 'action' => 'decision']);
        $routeCollector->addRoute('GET', '/domains/decision/', ['controller' => 'Domains', 'action' => 'decision']);
        $routeCollector->addRoute('POST', '/orders/pay-offline', ['controller' => 'Orders', 'action' => 'payOffline']);
        $routeCollector->addRoute('POST', '/orders/pay-with-mobile-wallet', ['controller' => 'Orders', 'action' => 'payWithMobileWallet']);
        $routeCollector->addRoute('GET', '/payments/check', ['controller' => 'Payments', 'action' => 'check']);
        $routeCollector->addRoute('POST', '/payments/mobile-money', ['controller' => 'Payments', 'action' => 'mobileMoney']);
        $routeCollector->addRoute('GET', '/about-us', ['controller' => 'AboutUs', 'action' => 'overview']);
        // Backwards-compatible alias for existing navigation links/bookmarks.
        $routeCollector->addRoute('GET', '/aboutUs', ['controller' => 'AboutUs', 'action' => 'overview']);
        $routeCollector->addRoute(['POST', 'GET'], '/contact', ['controller' => 'Contact', 'action' => 'overview']);
        $routeCollector->addRoute('GET', '/packages', ['controller' => 'Packages', 'action' => 'overview']);
        $routeCollector->addRoute('GET', '/support', ['controller' => 'Support', 'action' => 'overview']);
        $routeCollector->addRoute('GET', '/support/terms', ['controller' => 'Support', 'action' => 'terms']);
        $routeCollector->addRoute('GET', '/support/privacy', ['controller' => 'Support', 'action' => 'privacy']);
    }),
];
