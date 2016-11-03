<?php

namespace Clue\Commander\Tokens;

use InvalidArgumentException;

class OptionToken implements TokenInterface
{
    private $name;

    public function __construct($name)
    {
        if (!isset($name[1]) || $name[0] !== '-') {
            throw new InvalidArgumentException('Option name must start with a dash');
        }
        if ($name[1] !== '-' && isset($name[3])) {
            throw new InvalidArgumentException('Short option name must consist of a single character');
        }
        if ($name[1] === '-' && !isset($name[3])) {
            throw new InvalidArgumentException('Long option must consist of at least two characters');
        }
        $this->name = $name;
    }

    public function matches(array &$input, array &$output)
    {
        $pos = array_search($this->name, $input);
        $dd = array_search('--', $input);
        if ($pos !== false && ($dd === false || $dd > $pos)) {
            unset($input[$pos]);
            $output[ltrim($this->name, '-')] = false;
            return true;
        }

        return false;
    }

    public function __toString()
    {
        return $this->name;
    }
}
