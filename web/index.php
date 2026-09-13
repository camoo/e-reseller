<?php

require dirname(__DIR__) . '/vendor/autoload.php';

$caller = new \CAMOO\Http\Caller(dirname(__DIR__) . '/config');
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
