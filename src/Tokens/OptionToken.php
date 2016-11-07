<?php

namespace Clue\Commander\Tokens;

use InvalidArgumentException;

class OptionToken implements TokenInterface
{
    private $name;
    private $placeholder;
    private $required;

    public function __construct($name, TokenInterface $placeholder = null, $required = false)
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

        if ($required && $placeholder === null) {
            throw new InvalidArgumentException('Requires a placeholder when option value is marked required');
        }

        $this->name = $name;
        $this->placeholder = $placeholder;
        $this->required = $required;
    }

    public function matches(array &$input, array &$output)
    {
        $len = strlen($this->name);
        $foundName = null;

        foreach ($input as $key => $value) {
            // already found a match with no value append in previous iteration
            if ($foundName !== null) {
                if (substr($value, 0, 1) === '-') {
                    // we expected a value but actually found another option

                    if ($this->required) {
                        // option requires a value => skip and keep searching
                        $foundName = null;
                    } else {
                        // option does not require a value, break in order to use `false`
                        break;
                    }
                } else {
                    // we found a value after the key in the previous iteration

                    if (!$this->validate($value)) {
                        if ($this->required) {
                            // option requires a valid value => skip and keep searching
                            $foundName = null;
                        } else {
                            // option does not require a valid value => break in order to use `false`
                            break;
                        }
                    } else {
                        unset($input[$foundName]);
                        unset($input[$key]);
                        $output[ltrim($this->name, '-')] = $value;
                        return true;
                    }
                }
            }

            if (strpos($value, $this->name) === 0) {
                // found option with this prefix

                if ($value === $this->name) {
                    // this is an exact match (no value appended)

                    // if this accepts a value, check next iteration for value
                    if ($this->placeholder !== null) {
                        $foundName = $key;
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

                if (!$this->validate($value)) {
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

        if ($foundName !== null && !$this->required) {
            // found a key in the last iteration and no following value
            unset($input[$foundName]);
            $output[ltrim($this->name, '-')] = false;
            return true;
        }

        return false;
    }

    public function __toString()
    {
        $ret = $this->name;

        if ($this->placeholder !== null) {
            if ($this->required) {
                if ($this->placeholder instanceof SentenceToken || $this->placeholder instanceof AlternativeToken || $this->placeholder instanceof EllipseToken) {
                    $ret .= '=(' . $this->placeholder . ')';
                } else {
                    $ret .= '=' . $this->placeholder;
                }
            } else {
                $ret .= '[=' . $this->placeholder . ']';
            }
        }

        return $ret;
    }

    private function validate(&$value)
    {
        if ($this->placeholder !== null) {
            $input = array($value);
            $output = array();

            // filter the value through the placeholder
            if (!$this->placeholder->matches($input, $output)) {
                return false;
            }

            // if the placeholder returned a filtered value, use this one
            if ($output) {
                $value = reset($output);
            }
        }
        return true;
    }
}
