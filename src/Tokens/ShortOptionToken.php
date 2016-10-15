<?php

namespace Clue\Commander\Tokens;

use InvalidArgumentException;

class ShortOptionToken implements TokenInterface
{
    private $name;

    public function __construct($name)
    {
        if (!isset($name[0]) || isset($name[1])) {
            throw new InvalidArgumentException('Short option name must consist of a single character');
        }
        $this->name = $name;
    }

    public function matches(array &$input, array &$output)
    {
        $pos = array_search('-' . $this->name, $input);
        if ($pos !== false) {
            unset($input[$pos]);
            $output[$this->name] = false;
            return true;
        }

        return false;
    }

    public function __toString()
    {
        return '-' . $this->name;
    }
}
