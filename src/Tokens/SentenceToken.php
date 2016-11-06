<?php

namespace Clue\Commander\Tokens;

use InvalidArgumentException;

class SentenceToken implements TokenInterface
{
    private $tokens = array();

    public function __construct(array $tokens)
    {
        foreach ($tokens as $token) {
            if (!$token instanceof TokenInterface) {
                throw new InvalidArgumentException('Sentence must only contain valid tokens');
            } elseif ($token instanceof self) {
                // merge any tokens from sub-sentences
                foreach ($token->tokens as $token) {
                    $this->tokens []= $token;
                }
            } else {
                $this->tokens []= $token;
            }
        }

        if (count($this->tokens) < 2) {
            throw new InvalidArgumentException('Sentence must contain at least 2 tokens');
        }
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
        return implode(' ', array_map(function (TokenInterface $token) {
            // alternative token should be surrounded in parentheses
            if ($token instanceof AlternativeToken) {
                return '(' . $token . ')';
            }
            return (string)$token;
        }, $this->tokens));
    }
}
