<?php

namespace Clue\Commander\Tokens;

class WordToken implements TokenInterface
{
    private $word;

    public function __construct($word)
    {
        $this->word = $word;
    }

    public function matches(array &$input, array &$output)
    {
        if (isset($input[0]) && $input[0] === $this->word) {
            array_shift($input);
            return true;
        }
        return false;
    }

    public function __toString()
    {
        return $this->word;
    }
}
