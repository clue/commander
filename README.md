# clue/commander [![Build Status](https://travis-ci.org/clue/php-commander.svg?branch=master)](https://travis-ci.org/clue/php-commander)

Finally a sane way to register available commands and arguments and match your command line in PHP.

You want to build a command line interface (CLI) tool in PHP which accepts
additional arguments and you now want to route these to individual functions?
Then this library is for you!

This is also useful for interactive CLI tools or anywhere where you can break up
a command line string into an array of command line arguments and you now want
to execute individual functions depending on the arguments given.

**Table of contents**

* [Quickstart example](#quickstart-example)
* [Usage](#usage)
  * [Router()](#router)
    * [add()](#add)
    * [remove()](#remove)
    * [getRoutes()](#getroutes)
    * [execArgv()](#execargv)
    * [handleArgv()](#handleargv)
    * [handleArgs()](#handleargs)
  * [Route](#route)
  * [NoRouteFoundException](#noroutefoundexception)
* [Install](#install)
* [License](#license)

### Quickstart example

The following example code demonstrates how this library can be used to build
a very simple command line interface (CLI) tool that accepts command line
arguments passed to this program:

```php
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
$router->add('', function () use ($router) {
    echo 'Usage:' . PHP_EOL;
    foreach ($router->getRoutes() as $route) {
        if ($route == '') continue;
        echo '  ' .$route . PHP_EOL;
    }
});

$router->execArgv();
```

See also the [examples](examples).

## Usage

### Router

The `Router` is the main class in this package.

It is responsible for registering new Routes, matching the given args against
these routes and then executing the registered route callback.

```php
$router = new Router();
```

#### add()

The `add(string $route, callable $handler) : Router` method can be used to
register a new [`Route`](#route) with this Router.

It accepts a route expression to match and a route callback that will be
executed when this route expression matches.

This is very similar to how common PHP (micro-)frameworks offer "HTTP routers"
to route incoming HTTP requests to the corresponding "controller functions":

```php
$route = $router->add($path, $fn);
```

The route expression aims to be so simple that both consumers of this library
(i.e. developers) and users of your resulting tools should be able to understand
them.

You can use any number of static keywords like this:

```php
$router->add('user list', function () {
    echo 'Here are all our users…' . PHP_EOL;
});
// matches: user list
// does not match: user (missing required keyword)
// does not match: user list hello (too many arguments)
```

Similarly, you can use an empty string like this to match when no arguments have been given:

```php
$router->add('', function() {
    echo 'No arguments given. Need help?' . PHP_EOL;
});
// matches: (empty string)
// does not match: hello (too many arguments)
```

You can use any number of placeholders to mark required arguments like this:

```php
$router->add('user add <name>', function (array $args) {
    assert(is_string($args['name']));
    var_dump($args['name']);
});
// matches: user add clue
// does not match: user add (missing required argument)
// does not match: user add hello world (too many arguments)
```

You can mark arguments as optional by enclosing them in square brackets like this:

```php
$router->add('user search [<query>]', function (array $args) {
    assert(!isset($args['query']) || is_string($args['query']));
    var_dump(isset($args['query']);
});
// matches: user search
// matches: user search clue
// does not match: user search hello world (too many arguments)
```

You can accept any number of arguments by appending ellipses like this:

```php
$router->add('user delete <names>...', function (array $args) {
    assert(is_array($args);
    assert(count($args) > 0);
    var_dump($args['names']);
});
// matches: user delete clue
// matches: user delete hello world
// does not match: user delete (missing required argument)
```

You can accept any number of optional arguments by appending ellipses within square brackets like this:

```php
$router->add('user dump [<names>...]', function (array $args) {
    if (isset($args['names'])) {
        assert(is_array($args);
        assert(count($args) > 0);
        var_dump($args['names']);
    } else {
        var_dump('no names');
    }
});
// matches: user dump
// matches: user dump clue
// matches: user dump hello world
```

#### remove()

The `remove(Router $route)` method can be used to remove the given
[`Route`](#route) object from the registered routes.

```php
$route = $router->add('hello <name>', $fn);
$router->remove($route);
```

It will throw an `UnderflowException` if the given route does not exist.

#### getRoutes()

The `getRoutes(): Route[]` method can be used to return an array of all
registered [`Route`](#route) objects.

```php
echo 'Usage help:' . PHP_EOL;
foreach ($router->getRoutes() as $route) {
    echo $route . PHP_EOL;
}
```

This array will be empty if you have not added any routes yet.

#### execArgv()

The `execArgv(array $argv = null) : void` method can be used to
execute by matching the `argv` against all registered routes and then exit.

You can explicitly pass in your `$argv` or it will automatically use the
values from the `$_SERVER` superglobal. The `argv` is an array that will
always start with the calling program as the first element. We simply
ignore this first element and then process the remaining elements
according to the registered routes.

This is a convenience method that will match and execute a route and then
exit the program without returning.

If no route could be found or if the route callback throws an Exception,
it will print out an error message to STDERR and set an appropriate
non-zero exit code.

Note that this is for convenience only and only useful for the most
simple of all programs. If you need more control, then consider using
the underlying [`handleArgv()`](#handleargv) method and handle any error situations
yourself.

#### handleArgv()

The `handleArgv(array $argv = null) : mixed` method can be used to
execute by matching the `argv` against all registered routes and then return.

You can explicitly pass in your `$argv` or it will automatically use the
values from the `$_SERVER` superglobal. The `argv` is an array that will
always start with the calling program as the first element. We simply
ignore this first element and then process the remaining elements
according to the registered routes.

Unlike [`execArgv()`](#execargv) this method will try to execute the route callback
and then return whatever the route callback returned.

```php
$router->add('hello <name>', function (array $args) {
    return strlen($args[$name]);
});

$length = $router->handleArgv(array('program', 'hello', 'test'));

assert($length === 4);
```

If no route could be found, it will throw a [`NoRouteFoundException`](#noroutefoundexception).

```php
// throws NoRouteFoundException
$router->handleArgv(array('program', 'invalid'));
```

If the route callback throws an `Exception`, it will pass through this `Exception`.

```php
$router->add('hello <name>', function (array $args) {
    if ($args['name'] === 'admin') {
        throw new InvalidArgumentException();
    }
    
    return strlen($args['name']);
});

// throws InvalidArgumentException
$router->handleArgv(array('program', 'hello', 'admin'));
```

#### handleArgs()

The `handleArgs(array $args) : mixed` method can be used to
execute by matching the given args against all registered routes and then return.

Unlike [`handleArgv()`](#handleargv) this method will use the complete `$args` array
to match the registered routes (i.e. it will not ignore the first element).
This is particularly useful if you build this array yourself or if you
use an interactive command line interface (CLI) and ask your user to
supply the arguments.

```php
$router->add('hello <name>', function (array $args) {
    return strlen($args[$name]);
});

$length = $router->handleArgs(array('hello', 'test'));

assert($length === 4);
```

The arguments have to be given as an array of individual elements. If you
only have a command line string that you want to split into an array of
individual command line arguments, consider using
[clue/arguments](https://github.com/clue/php-arguments).

```php
$line = fgets(STDIN, 2048);
assert($line === 'hello "Christian Lück"');

$args = Clue\Arguments\split($line);
assert($args === array('hello', 'Christian Lück'));

$router->handleArgs($args);
```

If no route could be found, it will throw a [`NoRouteFoundException`](#noroutefoundexception).

```php
// throws NoRouteFoundException
$router->handleArgs(array('invalid'));
```

If the route callback throws an `Exception`, it will pass through this `Exception`.

```php
$router->add('hello <name>', function (array $args) {
    if ($args['name'] === 'admin') {
        throw new InvalidArgumentException();
    }
    
    return strlen($args['name']);
});

// throws InvalidArgumentException
$router->handleArgs(array('hello', 'admin'));
```

### Route

The `Route` represents a single registered route within the [Router](#router).

It holds the required route tokens to match and the route callback to
execute if this route matches.

See [`Router`](#router).

### NoRouteFoundException

The `NoRouteFoundException` will be raised by [`handleArgv()`](#handleargv)
or [`handleArgs()`](#handleargs) if no matching route could be found.
It extends PHP's built-in `RuntimeException`.

## Install

The recommended way to install this library is [through Composer](http://getcomposer.org).
[New to Composer?](http://getcomposer.org/doc/00-intro.md)

This will install the latest supported version:

```bash
$ composer require clue/commander:^0.1
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

## License

MIT
