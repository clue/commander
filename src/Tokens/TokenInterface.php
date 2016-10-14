<?php

namespace Clue\Commander\Tokens;

interface TokenInterface
{
    /**
     * Tries to match the $input against this route
     *
     * The $input and $output arguments will both be given by reference.
     * - They may be modified when this route matches (i.e. this method returns
     *   true)
     * - They must not be modified if this route does not match (i.e. this method
     *   returns false).
     *
     * @param array $input  Remaining input args from the user input (tokens will pop from this vector)
     * @param array $output Output args that will be collected (tokens may push to this map)
     * @return boolean Returns whether the $input can be matched against this route
     */
    public function matches(array &$input, array &$output);

    /**
     * Returns a string representation for this route token
     *
     * @return string
     */
    public function __toString();
}
