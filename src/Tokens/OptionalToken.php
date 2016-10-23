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

        if ((string)$this === '[]') {
            throw new InvalidArgumentException('Optional block must not be empty');
        }
    }

    public function matches(array &$input, array &$output)
    {
        // input is empty or has only single double dash remaining
        if (!$input || (count($input) === 1 && reset($input) === '--')) {
            return true;
        }

        return $this->token->matches($input, $output);
    }

    public function __toString()
    {
        return '[' . $this->token . ']';
    }
}
