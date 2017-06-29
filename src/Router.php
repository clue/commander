<?php

namespace Clue\Commander;

use Clue\Commander\Tokens\Tokenizer;
use Exception;

/**
 * The Router is the main class in this package.
 *
 * It is responsible for registering new Routes, matching the given args against
 * these routes and then executing the registered route callback.
 */
class Router
{
    private $routes = array();
    private $tokenizer;

    /**
     * Instantiate new Router
     *
     * @param null|Tokenizer $tokenizer (optional) Tokenizer to use to create route tokens from given route expressions
     */
    public function __construct(Tokenizer $tokenizer = null)
    {
        if ($tokenizer === null) {
            $tokenizer = new Tokenizer();
        }

        $this->tokenizer = $tokenizer;
    }

    /**
     * Registers a new Route with this Router
     *
     * @param string   $route   the route expression to match
     * @param callable $handler route callback that will be executed when this route expression matches
     * @return Route
     * @throws InvalidArgumentException if the route expression is invalid
     */
    public function add($route, $handler)
    {
        if (trim($route) === '') {
            $token = null;
        } else {
            $token = $this->tokenizer->createToken($route);
        }
        $route = new Route($token, $handler);

        $this->routes[] = $route;

        return $route;
    }

    /**
     * Removes the given route object from the registered routes
     *
     * @param Route $route
     * @throws \UnderflowException if the route does not exist
     */
    public function remove(Route $route)
    {
        $id = array_search($route, $this->routes);
        if ($id === false) {
            throw new \UnderflowException('Given Route not found');
        }

        unset($this->routes[$id]);
    }

    /**
     * Returns an array of all registered routes
     *
     * @return Route[]
     */
    public function getRoutes()
    {
        return $this->routes;
    }

    /**
     * Executes by matching the `argv` against all registered routes and then exits
     *
     * This is a convenience method that will match and execute a route and then
     * exit the program without returning.
     *
     * If no route could be found or if the route callback throws an Exception,
     * it will print out an error message to STDERR and set an appropriate
     * non-zero exit code.
     *
     * Note that this is for convenience only and only useful for the most
     * simple of all programs. If you need more control, then consider using
     * the underlying `handleArgv()` method and handle any error situations
     * yourself.
     *
     * You can explicitly pass in your `argv` or it will automatically use the
     * values from the $_SERVER superglobal. The `argv` is an array that will
     * always start with the calling program as the first element. We simply
     * ignore this first element and then process the remaining elements
     * according to the registered routes.
     *
     * @param array $argv
     * @uses self::handleArgv()
     */
    public function execArgv(array $argv = null)
    {
        try {
            $this->handleArgv($argv);
            // @codeCoverageIgnoreStart
        } catch (NoRouteFoundException $e) {
            fwrite(STDERR, 'Usage Error: ' . $e->getMessage() . PHP_EOL);

            // sysexits.h: #define EX_USAGE 64 /* command line usage error */
            exit(64);
        } catch (Exception $e) {
            fwrite(STDERR, 'Program Error: ' . $e->getMessage() . PHP_EOL);

            // stdlib.h: #define EXIT_FAILURE 1
            exit(1);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Executes by matching the `argv` against all registered routes and then returns
     *
     * Unlike `execArgv()` this method will try to execute the route callback
     * and then return whatever the route callback returned.
     *
     * If no route could be found or if the route callback throws an Exception,
     * it will throw an Exception.
     *
     * You can explicitly pass in your `argv` or it will automatically use the
     * values from the $_SERVER superglobal. The `argv` is an array that will
     * always start with the calling program as the first element. We simply
     * ignore this first element and then process the remaining elements
     * according to the registered routes.
     *
     * @param array|null $argv
     * @return mixed will be the return value from the matched route callback
     * @throws Exception if the executed route callback throws an exception
     * @throws NoRouteFoundException If no matching route could be found
     * @uses self::handleArgs()
     */
    public function handleArgv(array $argv = null)
    {
        if ($argv === null) {
            $argv = isset($_SERVER['argv']) ? $_SERVER['argv'] : array();
        }
        array_shift($argv);

        return $this->handleArgs($argv);
    }

    /**
     * Executes by matching the given args against all registered routes and then returns
     *
     * Unlike `handleArgv()` this method will use the complete `$args` array
     * to match the registered routes (i.e. it will not ignore the first element).
     * This is particularly useful if you build this array yourself or if you
     * use an interactive command line interface (CLI) and ask your user to
     * supply the arguments.
     *
     * The arguments have to be given as an array of individual elements. If you
     * only have a command line string that you want to split into an array of
     * individual command line arguments, consider using clue/arguments.
     *
     * @param array $args
     * @return mixed will be the return value from the matched route callback
     * @throws Exception if the executed route callback throws an exception
     * @throws NoRouteFoundException If no matching route could be found
     */
    public function handleArgs(array $args)
    {
        foreach ($this->routes as $route) {
            $input = $args;
            $output = array();
            if ($route->matches($input, $output)) {
                return $route($output);
            }
        }

        throw new NoRouteFoundException('No matching route found');
    }
}
