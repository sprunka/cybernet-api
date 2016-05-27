<?php
//Help That shows what routes we have defined:
// $app->get('/', 'CybernetAPI\Route\Help'); // TODO: Make this route.
// $app->get('/help[/{what}]', 'CybernetAPI\Route\Help'); // TODO: Make this route.

// Help for each route.
// $app->get('/choose/help[/{what}]', 'CybernetAPI\Choose\Help'); // TODO: Make this route.
$app->get('/roll/help[/{what}]', 'CybernetAPI\Roll\Help');

//Simple Dice Roller
$app->get('/roll[/{pattern}[/{rules:.*}]]', 'CybernetAPI\Roll\Dice');

// Generate a name.
$app->get('/choose/name[/{gender}[/{firstLastFull}]]', 'CybernetAPI\Choose\PersonName');
