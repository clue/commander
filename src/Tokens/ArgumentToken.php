<?php

namespace Clue\Commander\Tokens;

class ArgumentToken implements TokenInterface
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function matches(array &$input, array &$output)
    {
        if ($input) {
            $value = array_shift($input);
            $output[$this->name] = $value;
            return true;
        }

        return false;
    }

    public function __toString()
    {
        return '<' . $this->name . '>';
    }
}
