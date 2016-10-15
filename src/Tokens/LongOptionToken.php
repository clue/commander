<?php

namespace Clue\Commander\Tokens;

use InvalidArgumentException;

class LongOptionToken implements TokenInterface
{
    private $name;

    public function __construct($name)
    {
        if (!isset($name[1])) {
            throw new InvalidArgumentException('Long option must consist of at least two characters');
        }
        $this->name = $name;
    }

    public function matches(array &$input, array &$output)
    {
        $pos = array_search('--' . $this->name, $input);
        if ($pos !== false) {
            unset($input[$pos]);
            $output[$this->name] = false;
            return true;
        }

        return false;
    }

    public function __toString()
    {
        return '--' . $this->name;
    }
}
