<?php

require __DIR__ . '/../vendor/autoload.php';

$router = new Clue\Commander\Router();
$router->add('exit [<code:uint>]', function (array $args) {
    exit(isset($args['code']) ? $args['code'] : 0);
});
$router->add('sleep <seconds:uint>', function (array $args) {
    sleep($args['seconds']);
});
$router->add('echo <words>...', function (array $args) {
    echo join(' ', $args['words']) . PHP_EOL;
});
$router->add('[--help | -h]', function () use ($router) {
    echo 'Usage:' . PHP_EOL;
    foreach ($router->getRoutes() as $route) {
        echo '  ' .$route . PHP_EOL;
    }
});

$router->execArgv();
