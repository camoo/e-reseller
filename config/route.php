<?php
$oRouteDispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $route) {
    $route->addRoute('GET', '/', ['controller' => 'Pages', 'action' => 'overview']);
    $route->addRoute('POST', '/login', ['controller' => 'Users', 'action' => 'login']);
    $route->addRoute('POST', '/join', ['controller' => 'Users', 'action' => 'join']);
    $route->addRoute('GET', '/logout', ['controller' => 'Users', 'action' => 'logout']);
    $route->addRoute('GET', '/sso', ['controller' => 'Users', 'action' => 'getSSO']);
    $route->addRoute('POST', '/domain-whois', ['controller' => 'Domains', 'action' => 'domainSearch']);
    // ..
});
