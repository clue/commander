<?php

namespace Clue\Commander\Tokens;

use InvalidArgumentException;

class OptionToken implements TokenInterface
{
    private $name;
    private $placeholder;
    private $required;

    public function __construct($name, $placeholder = null, $required = false)
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

        if ($placeholder !== null && !isset($placeholder[0])) {
            throw new InvalidArgumentException('Option placeholder must not be empty');
        }
        if ($required && $placeholder === null) {
            throw new InvalidArgumentException('Requires placeholder name when option value is required');
        }

        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->required = $required;
    }

    public function matches(array &$input, array &$output)
    {
        $len = strlen($this->name);

        foreach ($input as $key => $value) {
            if (strpos($value, $this->name) === 0) {
                // found option with this prefix

                if ($value === $this->name) {
                    // this is an exact match (no value appended)
                    if ($this->required) {
                        // value is required => keep searching
                        continue;
                    }

                    // use `false` value for compatibility with `getopt()` etc.
                    $value = false;
                } elseif ($this->placeholder !== null && $value[$len] === '=') {
                    // value accepted and actually appended
                    // actual option value is everything after `=`
                    $value = substr($value, $len + 1);
                } elseif ($this->placeholder !== null && $this->name[1] !== '-') {
                    // value accepted and appended right after option name (only for short options)
                    $value = substr($value, $len);
                } else {
                    // no value accepted or not followed by `=` => keep searching
                    continue;
                }

                unset($input[$key]);
                $output[ltrim($this->name, '-')] = $value;
                return true;
            } elseif ($value === '--') {
                // double dash found => no option after this point
                break;
            }
        }

        return false;
    }

    public function __toString()
    {
        $ret = $this->name;

        if ($this->placeholder !== null) {
            if (!$this->required) {
                $ret .= '[';
            }
            $ret .= '=<' . $this->placeholder . '>';
            if (!$this->required) {
                $ret .= ']';
            }
        }

        return $ret;
    }
}
