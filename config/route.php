<?php
$oRouteDispatcher = FastRoute\simpleDispatcher(function (FastRoute\RouteCollector $route) {
    $route->addRoute('GET', '/', ['controller' => 'Pages', 'action' => 'overview']);
    // ..
});
