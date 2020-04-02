<?php
$oRouteDispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $route) {
    $route->addRoute('GET', '/', ['controller' => 'Pages', 'action' => 'overview']);
    $route->addRoute(['GET', 'POST'], '/login', ['controller' => 'Users', 'action' => 'login']);
    $route->addRoute(['GET', 'POST'], '/join', ['controller' => 'Users', 'action' => 'join']);
    // ..
});
