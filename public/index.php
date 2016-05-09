<?php

require '../vendor/autoload.php';
$config = [];
require_once '../config/general.php';
require_once '../config/db.php';

$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();
// Configure the Containers.
require_once '../config/containers.php';

// Routes
require_once '../routing/routes.php';

$app->run();
