<?php

namespace Clue\Commander;

use Clue\Commander\Tokens\TokenInterface;
use InvalidArgumentException;

/**
 * The Route represents a single registered route within the Router
 *
 * It holds the required route tokens to match and the route callback to
 * execute if this route matches.
 *
 * @see Router
 */
class Route implements TokenInterface
{
    private $token;

    /**
     * Instantiate new Router object
     *
     * @param TokenInterface|null $token   the optional route token to match. If no token is given, this matches only the empty input.
     * @param callable            $handler the route callback to execute if this route matches
     * @throws InvalidArgumentException if the given $handler is not a valid callable
     */
    public function __construct(TokenInterface $token = null, $handler)
    {
        if (!is_callable($handler)) {
            throw new InvalidArgumentException('Route handler is not a valid callable');
        }

        $this->token = $token;
        $this->handler = $handler;
    }

    /**
     * Matches this route against the given $input arguments
     *
     * @param array $input
     * @param array $output
     * @return boolean
     * @see TokenInterface::matches()
     */
    public function matches(array &$input, array &$output)
    {
        if ($this->token === null || $this->token->matches($input, $output)) {
            // excessive arguments should fail, make sure input is now empty
            // single remaining `--` to separate options from arguments is also accepted
            if (!$input || (count($input) === 1 && reset($input) === '--')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a string representation for this route token
     *
     * @return string
     */
    public function __toString()
    {
        return (string)$this->token;
    }

    /**
     * Invokes the route callback handler
     *
     * @param array $args the callback arguments ($output args aquired by matching route tokens)
     * @return mixed returns whatever the callback handler returns
     * @throws Exception throws any exception the callback handler may throw
     */
    public function __invoke(array $args)
    {
        return call_user_func($this->handler, $args);
    }
}
