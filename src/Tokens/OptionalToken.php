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
