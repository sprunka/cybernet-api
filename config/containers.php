<?php

$container['logger'] = function ($c) {
    $logger = new \Monolog\Logger('cybernet-api');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/cybernet-api.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['faker'] = function ($c) {
    $faker = Faker\Factory::create();
    return $faker;
};

$container['csrf'] = function ($c) {
    return new \Slim\Csrf\Guard;
};
