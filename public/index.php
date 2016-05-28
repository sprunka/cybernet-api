<?php
use Psr7Middlewares\Middleware;

// Start PHP session
putenv("ENV=dev");
session_start();


require '../vendor/autoload.php';
$config = [];
require_once '../config/general.php';
require_once '../config/db.php';

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();
// Configure the Containers.
require_once '../config/containers.php';

$app->add(Middleware::TrailingSlash(false)->redirect(301))
    ->add(Middleware::ResponseTime())
    ->add(Middleware::FormatNegotiator()->defaultFormat('json'))
    ->add($container->get('csrf'));


require_once '../routing/routes.php';
$app->run();
