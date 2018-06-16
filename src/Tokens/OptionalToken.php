<?php

namespace Clue\Commander\Tokens;

use InvalidArgumentException;

class OptionalToken implements TokenInterface
{
    private $token;

    public function __construct(TokenInterface $token)
    {
        if ($token instanceof self) {
            throw new InvalidArgumentException('Nested optional block is superfluous');
        }

        $this->token = $token;
    }

    public function matches(array &$input, array &$output)
    {
        // try greedy match for sub-token or succeed anyway
        $this->token->matches($input, $output);

        return true;
    }

    public function __toString()
    {
        return '[' . $this->token . ']';
    }
}
