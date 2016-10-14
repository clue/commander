<?php

require __DIR__ . '/../vendor/autoload.php';

$router = new Clue\Commander\Router();
$router->add('exit [<code>]', function (array $args) {
    exit(isset($args['code']) ? (int)$args['code'] : 0);
});
$router->add('sleep <seconds>', function (array $args) {
    sleep($args['seconds']);
});
$router->add('echo <words>...', function (array $args) {
    echo join(' ', $args['words']) . PHP_EOL;
});
$router->add('help', function () use ($router) {
    echo 'Usage:' . PHP_EOL;
    foreach ($router->getRoutes() as $route) {
        echo '  ' .$route . PHP_EOL;
    }
});

echo 'Hello! Try the sleep, echo and exit commands.' . PHP_EOL;

while (true) {
    echo '> ';
    $line = fgets(STDIN, 1024);

    // stop loop if STDIN is no longer readable
    if ($line === false) {
        break;
    }

    // try to parse command line or complain
    try {
        $args = Clue\Arguments\split($line);
    } catch (\RuntimeException $e) {
        echo 'Invalid command line. Missing quotes?' . PHP_EOL;
        continue;
    }

    // skip empty lines
    if (!$args) {
        continue;
    }

    // process the given#
    try {
        $router->handleArgs($args);
    } catch (Clue\Commander\NoRouteFoundException $e) {
        echo 'Usage error. ' . $e->getMessage() . PHP_EOL;
    }
}
