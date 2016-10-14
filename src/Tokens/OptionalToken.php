<?php

namespace Clue\Commander\Tokens;

class OptionalToken implements TokenInterface
{
    private $token;

    public function __construct(TokenInterface $token)
    {
        $this->token = $token;
    }

    public function matches(array &$input, array &$output)
    {
        if (!$input) {
            return true;
        }

        return $this->token->matches($input, $output);
    }

    public function __toString()
    {
        return '[' . $this->token . ']';
    }
}
