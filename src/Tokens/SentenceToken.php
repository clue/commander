<?php

namespace Clue\Commander\Tokens;

class SentenceToken implements TokenInterface
{
    private $tokens;

    public function __construct(array $tokens)
    {
        $this->tokens = $tokens;
    }

    public function matches(array &$input, array &$output)
    {
        $sinput = $input;
        $soutput = $output;

        foreach ($this->tokens as $token) {
            if (!$token->matches($input, $output)) {
                $input = $sinput;
                $output = $soutput;
                return false;
            }
        }

        return true;
    }

    public function __toString()
    {
        $ret = '';

        foreach ($this->tokens as $token) {
            if ($ret !== '') {
                $ret .= ' ';
            }

            $ret .= $token;
        }

        return $ret;
    }
}
