<?php
/**
 * If you have access to command line, setup the enviroment var ENV to equal your current enviornment:
 * dev, stage, prod
 * If you do not have access to environment vars, or
 * if you do not set a value for ENV the app will run in production mode.
 */
$environment = (getenv('ENV')?:'prod');
define('ENV', $environment);

// Only use true in development environments.
if (ENV == 'dev') {
    $config['displayErrorDetails'] = true;
}
