<?php
use Psr7Middlewares\Middleware;

putenv("ENV=dev");


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
    ->add(Middleware::FormatNegotiator()->defaultFormat('json'));

require_once '../routing/routes.php';

$app->run();
