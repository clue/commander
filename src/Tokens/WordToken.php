<?php

namespace Clue\Commander\Tokens;

use InvalidArgumentException;

class WordToken implements TokenInterface
{
    private $word;

    public function __construct($word)
    {
        if (!isset($word[0])) {
            throw new InvalidArgumentException('Word must not be empty');
        }
        $this->word = $word;
    }

    public function matches(array &$input, array &$output)
    {
        foreach ($input as $key => $value) {
            if ($value === $this->word) {
                unset($input[$key]);
                return true;
            } elseif ($value === '' || $value[0] !== '-') {
                // any other word/argument (non-option) found => fail
                break;
            }
        }
        return false;
    }

    public function __toString()
    {
        return $this->word;
    }
}
