<?php

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

// Default Hello World Route.
$app->get('/hello/{name}', function (Request $request, Response $response) {
    $name = $request->getAttribute('name');
    $this->logger->info("Something interesting happened");
    $response->getBody()->write("Hello, $name");
    $response->getBody()->write("\n<pre>\n" . print_r($this, true) . '</pre>');

    return $response;
});

//Simple Dice Roller
$app->get('/roll[/{pattern}[/{rule}]]', 'CybernetAPI\Roll\Dice');

//TODO: Implement allowance for multiple rules?
//$app->get('/roll[/{pattern}[/{rules:.*}]]', 'CybernetAPI\Roll\Dice');
