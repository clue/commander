<?php

namespace Clue\Commander\Tokens;

use InvalidArgumentException;

class ArgumentToken implements TokenInterface
{
    private $name;
    private $filter;

    public function __construct($name, $filter = null)
    {
        if (!isset($name[0])) {
            throw new InvalidArgumentException('Empty argument name');
        }
        $this->name = $name;
        $this->filter = $filter;

        $demo = '';
        $this->validate($demo, false);
    }

    public function matches(array &$input, array &$output)
    {
        $dd = false;
        foreach ($input as $key => $value) {
            if ($this->validate($value, $dd)) {
                unset($input[$key]);
                $output[$this->name] = $value;
                return true;
            } elseif ($value === '' || $value[0] !== '-' || $dd) {
                // this not an option => it should have matched => fail
                break;
            } elseif ($value === '--') {
                // found a double dash => following must be an argument
                $dd = true;
            }
        }

        return false;
    }

    public function __toString()
    {
        $ret = '<' . $this->name;
        if ($this->filter !== null) {
            $ret .= ':' . $this->filter;
        }
        $ret .= '>';

        return $ret;
    }

    private function validate(&$value, $dd)
    {
        if ($this->filter === null) {
            // value must not start with a dash (`-`), unless it's behind a double dash (`--`)
            return ($dd || $value === '' || $value[0] !== '-');
        } elseif ($this->filter === 'int' || $this->filter === 'uint') {
            $ret = filter_var($value, FILTER_VALIDATE_INT);
            if ($ret === false || ($this->filter === 'uint' && $ret < 0)) {
                return false;
            }
            $value = $ret;
            return true;
        } elseif ($this->filter === 'float' || $this->filter === 'ufloat') {
            $ret = filter_var($value, FILTER_VALIDATE_FLOAT);
            if ($ret === false || ($this->filter === 'ufloat' && $ret < 0)) {
                return false;
            }
            $value = $ret;
            return true;
        } elseif ($this->filter === 'bool') {
            $ret = filter_var($value, FILTER_VALIDATE_BOOLEAN, array('flags' => FILTER_NULL_ON_FAILURE));
            if ($ret === null) {
                return false;
            }
            $value = $ret;
            return true;
        } else {
            throw new \InvalidArgumentException('Invalid filter name');
        }
    }
}
