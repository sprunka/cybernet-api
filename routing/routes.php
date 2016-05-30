<?php
//Help That shows what routes we have defined:
$app->get('/', 'CybernetAPI\Help');
$app->get('/help[/{what:.*}]', 'CybernetAPI\Help');

// Help for each route.
//TODO: Write help documentation
$app->get('/choose/help[/{what:.*}]', 'CybernetAPI\Help');
$app->get('/roll/help[/{what:.*}]', 'CybernetAPI\Help');

//StatBlock Generator
$app->get('/roll/stats[/{variant}]', 'CybernetAPI\Roll\StatBlock');
//Simple Dice Roller
$app->get('/roll[/{pattern}[/{rules:.*}]]', 'CybernetAPI\Roll\Dice');

// Generate a name.
$app->get('/choose/name[/{gender}[/{firstLastFull}]]', 'CybernetAPI\Choose\PersonName');
