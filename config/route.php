<?php

declare(strict_types=1);

return [
    FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $routeCollector): void {
        $routeCollector->addRoute('GET', '/', ['controller' => 'Pages', 'action' => 'overview']);
        $routeCollector->addRoute(['POST', 'GET'], '/login', ['controller' => 'Users', 'action' => 'login']);
        $routeCollector->addRoute('GET', '/register', ['controller' => 'Users', 'action' => 'register']);
        $routeCollector->addRoute(['POST', 'GET'], '/join', ['controller' => 'Users', 'action' => 'join']);
        $routeCollector->addRoute('POST', '/logout', ['controller' => 'Users', 'action' => 'logout']);
        $routeCollector->addRoute('GET', '/sso', ['controller' => 'Users', 'action' => 'getSSO']);
        $routeCollector->addRoute('GET', '/balance', ['controller' => 'Users', 'action' => 'getBalance']);
        $routeCollector->addRoute('POST', '/profile/edit', ['controller' => 'Users', 'action' => 'editProfile']);
        $routeCollector->addRoute('POST', '/contacts/edit', ['controller' => 'Domains', 'action' => 'editContact']);
        $routeCollector->addRoute('POST', '/domains/resend-verification', ['controller' => 'Domains', 'action' => 'resendVerification']);
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
        $routeCollector->addRoute('POST', '/newsletter/subscribe', ['controller' => 'Newsletter', 'action' => 'subscribe']);
        $routeCollector->addRoute('GET', '/packages', ['controller' => 'Packages', 'action' => 'overview']);
        $routeCollector->addRoute('GET', '/support', ['controller' => 'Support', 'action' => 'overview']);
        $routeCollector->addRoute('GET', '/support/terms', ['controller' => 'Support', 'action' => 'terms']);
        $routeCollector->addRoute('GET', '/support/privacy', ['controller' => 'Support', 'action' => 'privacy']);
        $routeCollector->addRoute('GET', '/robots.txt', ['controller' => 'WellKnown', 'action' => 'robots']);
        $routeCollector->addRoute('GET', '/sitemap.xml', ['controller' => 'WellKnown', 'action' => 'sitemap']);
        $routeCollector->addRoute('GET', '/llms.txt', ['controller' => 'WellKnown', 'action' => 'llms']);
        $routeCollector->addRoute('GET', '/llm.txt', ['controller' => 'WellKnown', 'action' => 'llms']);
        $routeCollector->addRoute('GET', '/.well-known/security.txt', ['controller' => 'WellKnown', 'action' => 'securityTxt']);
        $routeCollector->addRoute('GET', '/security.txt', ['controller' => 'WellKnown', 'action' => 'securityTxt']);
        $routeCollector->addRoute('GET', '/.well-known/change-password', ['controller' => 'WellKnown', 'action' => 'changePassword']);
        $routeCollector->addRoute('GET', '/.well-known/{resource:.+}', ['controller' => 'WellKnown', 'action' => 'resource']);
    }),
];
