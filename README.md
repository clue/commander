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
  * [Router](#router)
    * [add()](#add)
    * [remove()](#remove)
    * [getRoutes()](#getroutes)
    * [execArgv()](#execargv)
    * [handleArgv()](#handleargv)
    * [handleArgs()](#handleargs)
  * [Route](#route)
  * [NoRouteFoundException](#noroutefoundexception)
* [Install](#install)
* [Tests](#tests)
* [License](#license)
* [More](#more)

### Quickstart example

The following example code demonstrates how this library can be used to build
a very simple command line interface (CLI) tool that accepts command line
arguments passed to this program:

```php
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

> Advanced usage: The `Router` accepts an optional [`Tokenizer`](#tokenizer)
  instance as the first parameter to the constructor.

#### add()

The `add(string $route, callable $handler): Route` method can be used to
register a new [`Route`](#route) with this Router.

It accepts a route expression to match and a route callback that will be
executed when this route expression matches.

This is very similar to how common PHP (micro-)frameworks offer "HTTP routers"
to route incoming HTTP requests to the corresponding "controller functions":

```php
$route = $router->add($path, $fn);
```

The route expression uses a custom domain-specific language (DSL) which aims to
be so simple that both consumers of this library
(i.e. developers) and users of your resulting tools should be able to understand
them.

Note that this is a left-associative grammar (LAG) and all tokens are greedy.
This means that the tokens will be processed from left to right and each token
will try to match as many of the input arguments as possible.
This implies that certain route expressions make little sense, such as having
an optional argument after an argument with ellipses.
For more details, see below.

You can use an empty string like this to match when no arguments have been given:

```php
$router->add('', function() {
    echo 'No arguments given. Need help?' . PHP_EOL;
});
// matches: (empty string)
// does not match: hello (too many arguments)
```

You can use any number of static keywords like this:

```php
$router->add('user list', function () {
    echo 'Here are all our users…' . PHP_EOL;
});
// matches: user list
// does not match: user (missing required keyword)
// does not match: user list hello (too many arguments)
```

You can use alternative blocks to support any of the static keywords like this:

```php
$router->add('user (list | listing | ls)', function () {
    echo 'Here are all our users…' . PHP_EOL;
});
// matches: user list
// matches: user listing
// matches: user ls
// does not match: user (missing required keyword)
// does not match: user list hello (too many arguments)
```

Note that alternative blocks can be added to pretty much any token in your route
expression.
Note that alternative blocks do not require parentheses and the alternative mark
(`|`) always works at the current block level, which may not always be obvious.
Unless you add some parentheses, `a b | c d` will be be interpreted as
`(a b) | (c d)` by default.
Parentheses can be used to interpret this as `a (b | c) d` instead.
In particular, you can also combine alternative blocks with optional blocks
(see below) in order to optionally accept only one of the alternatives, but not
multiple.

You can use any number of placeholders to mark required arguments like this:

```php
$router->add('user add <name>', function (array $args) {
    assert(is_string($args['name']));
    var_dump($args['name']);
});
// matches: user add clue
// does not match: user add (missing required argument)
// does not match: user add hello world (too many arguments)
// does not match: user add --test (argument looks like an option)

// matches: user add -- clue     (value: clue)
// matches: user add -- --test   (value: --test)
// matches: user add -- -nobody- (value: -nobody-)
// matches: user add -- --       (value: --)
```

Note that arguments that start with a dash (`-`) are not simply accepted in the
user input, because they may be confused with (optional) options (see below).
If users wish to process arguments that start with a dash (`-`), they either
have to use filters (see below) or may use a double dash separator (`--`),
as everything after this separator will be processed as-is.
See also the last examples above that demonstrate this behavior.

You can use one the predefined filters to limit what values are accepted like this:

```php
$router->add('user ban <id:int> <force:bool>', function (array $args) {
    assert(is_int($args['id']));
    assert(is_bool($args['force']));
});
// matches: user ban 10 true
// matches: user ban 10 0
// matches: user ban -10 yes
// matches: user ban -- -10 no
// does not match: user ban 10 (missing required argument)
// does not match: user ban hello true (invalid value does not validate)
```

Note that the filters also return the value casted to the correct data type.
Also note how using the double dash separator (`--`) is optional when matching
a filtered value.
The following predefined filters are currently available:

* `int` accepts any positive or negative integer value, such as `10` or `-4`
* `uint` accepts any positive (unsigned) integer value, such `10` or `0`
* `float` accepts any positive or negative float value, such as `1.5` or `-2.3`
* `ufloat` accepts any positive (unsigned) float value, such as `1.5` or `0`
* `bool` accepts any boolean value, such as `yes/true/1` or `no/false/0`

> If you want to add a custom filter function, see also [`Tokenizer`](#tokenizer)
  for advanced usage below.

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

Note that square brackets can be added to pretty much any token in your route
expression, however they are most commonly used for arguments as above or for
optional options as below.
Optional tokens can appear anywhere in the route expression, but keep in mind
that the tokens will be matched from left to right, so if the optional token
matches, then the remainder will be processed by the following tokens.
As a rule of thumb, make sure optional tokens are near the end of your route
expressions and you won't notice this subtle effect.
Optional blocks accept alternative groups, so that `[a | b]` is actually
equivalent to the longer form `[(a | b)]`.
In particular, this is often used for alternative options as below.

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

Note that trailing ellipses can be added to any argument, word or option token
in your route expression. They are most commonly used for arguments as above.
The above requires at least one argument, see the following if you want this
to be completely optional.
Technically, the ellipse tokens can appear anywhere in the route expression, but
keep in mind that the tokens will be matched from the left to the right, so if
the ellipse matches, it will consume all input arguments and not leave anything
for following tokens.
As a rule of thumb, make sure ellipse tokens are near the end of your route
expression and you won't notice this subtle effect.

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

The above does not require any arguments, it works with zero or more arguments.

You can add any number of optional short or long options like this:

```php
$router->add('user list [--json] [-f]', function (array $args) {
    assert(!isset($args['json']) || $args['json'] === false);
    assert(!isset($args['f']) || $args['f'] === false);
});
// matches: user list
// matches: user list --json
// matches: user list -f
// matches: user list -f --json
// matches: user -f list
// matches: --json user list
```

As seen in the example, options in the `$args` array can either be unset when
they have not been passed in the user input or set to `false` when they have
been passed (which is in line with how other parsers such as `getopt()` work).
Note that options are accepted anywhere in the user input argument, regardless
of where they have been defined.
Note that the square brackets are in the route expression are required to mark
this optional as optional, you can also omit these square brackets if you really
want a required option.

You can combine short and long options in an alternative block like this:

```php
$router->add('user setup [--help | -h]', function (array $args) {
    assert(!isset($args['help']) || $args['help'] === false);
    assert(!isset($args['h']) || $args['h'] === false);
    assert(!isset($args['help'], $args['h']); 
});
// matches: user setup
// matches: user setup --help
// matches: user setup -h
// does not match: user setup --help -h (only accept eithers, not both)
```

As seen in the example, this optionally accepts either the short or the long
option anywhere in the user input, but never both at the same time.

You can optionally accept or require values for short and long options like this:

```php
$router->add('[--sort[=<param>]] [-i=<start:int>] user list', function (array $args) {
    assert(!isset($args['sort']) || $args['sort'] === false || is_string($args['sort']));
    assert(!isset($args['i']) || is_int($args['i']));
});
// matches: user list
// matches: user list --sort
// matches: user list --sort=size
// matches: user list --sort size
// matches: user list -i=10
// matches: user list -i 10
// matches: user list -i10
// matches: user list -i=-10
// matches: user list -i -10
// matches: user list -i-10
// matches: user -i=10 list
// matches: --sort -- user list
// matches: --sort size user list
// matches: user list --sort -i=10
// does not match: user list -i (missing option value)
// does not match: user list -i --sort (missing option value)
// does not match: user list -i=a (invalid value does not validate)
// does not match: --sort user list (user will be interpreted as option value)
// does not match: user list --sort -2 (value looks like an option)
```

As seen in the example, option values in the `$args` array will be given as
strings or their filtered and casted value if passed in the user input.
Both short and long options can accept values with the recommended equation
symbol syntax (`-i=10` and `--sort=size`  respectively) in the user input.
Both short and long options can also accept values with the common space-separated
syntax (`-i 10` and `--sort size` respectively) in the user input.
Short options can also accept values with the common concatenated syntax
with no separator inbetween (`-i10`) in the user input.
Note that it is highly recommended to always make sure any options that accept
values are near the left side of your route expression.
This is needed in order to make sure space-separated values are consumed as
option values instead of being misinterpreted as keywords or arguments.

You can limit the values for short and long options to a given preset like this:

```php
$router->add('[--ask=(yes | no)] [-l[=0]] user purge', function (array $args) {
    assert(!isset($args['ask']) || $args['sort'] === 'yes' || $args['sort'] === 'no');
    assert(!isset($args['l']) || $args['l'] === '0');
});
// matches: user purge
// matches: user purge --ask=yes
// matches: user purge --ask=no
// matches: user purge -l
// matches: user purge -l=0
// matches: user purge -l 0
// matches: user purge -l0
// matches: user purge -l --ask=no
// does not match: user purge --ask (missing option value)
// does not match: user purge --ask=maybe (invalid option value)
// does not match: user purge -l4 (invalid option value)
```

As seen in the example, option values can be restricted to a given preset of
values by using any of the above tokens.
Technically, it's valid to use any of the above tokens to restrict the option
values.
In practice, this is mostly used for static keyword tokens or alternative groups
thereof.
It's recommended to always use parentheses for optional groups, however they're
not strictly required within options with optional values.
This also helps making it more obvious `[--ask=(yes | no)]` would accept either
option value, while the (less useful) expression `[--ask=yes | no]` would
accept either the option `--ask=yes` or the static keyword `no`.

#### remove()

The `remove(Route $route): void` method can be used to remove the given
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

The `execArgv(array $argv = null): void` method can be used to
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

The `handleArgv(array $argv = null): mixed` method can be used to
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

The `handleArgs(array $args): mixed` method can be used to
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

### Tokenizer

The `Tokenizer` class is responsible for parsing a route expression into a
valid token instance.
This class is mostly used internally and not something you have to worry about
in most cases.

If you need custom logic for your route expression, you may explicitly pass an
instance of your `Tokenizer` to the constructor of the `Router`:

```php
$tokenizer = new Tokenizer();

$router = new Router($tokenizer);
```

#### addFilter()

The `addFilter(string $name, callable $filter): void` method can be used to
add a custom filter function.

The filter name can then be used in argument or option expressions such as
`add <name:lower>` or `--search=<address:ip>`.

The filter function will be invoked with the filter value and MUST return a
boolean success value if this filter accepts the given value.
The filter value will be passed by reference, so it can be updated if the
filtering was successful.

```php
$tokenizer = new Tokenizer();
$tokenizer->addFilter('ip', function ($value) {
    return filter_var($ip, FILTER_VALIDATE_IP);
});
$tokenizer->addFilter('lower', function (&$value) {
    $value = strtolower($value);
    return true;
});

$router = new Router($tokenizer);
$router->add('add <name:lower>', function ($args) { });
$router->add('--search=<address:ip>', function ($args) { });
```

## Install

The recommended way to install this library is [through Composer](http://getcomposer.org).
[New to Composer?](http://getcomposer.org/doc/00-intro.md)

This will install the latest supported version:

```bash
$ composer require clue/commander:^1.3
```

See also the [CHANGELOG](CHANGELOG.md) for details about version upgrades.

## Tests

To run the test suite, you first need to clone this repo and then install all
dependencies [through Composer](http://getcomposer.org):

```bash
$ composer install
```

To run the test suite, go to the project root and run:

```bash
$ php vendor/bin/phpunit
```

## License

MIT

## More

* If you want to build an interactive CLI tool, you may want to look into using
  [clue/stdio-react](https://github.com/clue/php-stdio-react) in order to react
  to commands from STDIN.
* If you build an interactive CLI tool that reads a command line from STDIN, you
  may want to use [clue/arguments](https://github.com/clue/php-arguments) in
  order to split this string up into its individual arguments.
