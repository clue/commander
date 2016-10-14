<?php

namespace Clue\Commander\Tokens;

use InvalidArgumentException;

/**
 * The Tokenizer is responsible for breaking down the route expression into an internal syntax tree
 *
 * The Tokenizer is mostly used by the Router and there's little use in using
 * this outside this class.
 */
class Tokenizer
{
    /**
     * Creates a Token from the given route expression
     *
     * @param string $input
     * @return TokenInterface
     * @throws InvalidArgumentException if the route expression can not be parsed
     */
    public function createToken($input)
    {
        // whitespace characters to ignore
        static $ws = array(
            ' ',
            "\t",
            "\r",
            "\n",
        );

        $i = 0;
        $tokens = array();

        while (true) {
            // skip whitespace characters
            for (;isset($input[$i]) && in_array($input[$i], $ws); ++$i);

            // end of input reached
            if (!isset($input[$i])) {
                break;
            }

            if ($input[$i] === '<') {
                // start of argument found, search end token `>`
                for ($start = $i++; isset($input[$i]) && $input[$i] !== '>'; ++$i);

                // no end token found, syntax error
                if (!isset($input[$i])) {
                    throw new InvalidArgumentException('Missing end of argument');
                }

                // everything between `<` and `>` is the argument name
                $word = substr($input, $start + 1, $i++ - $start - 1);
                $token = new ArgumentToken($word);

                // followed by `...` means that any number of arguments are accepted
                if (substr($input, $i, 3) === '...') {
                    $token = new EllipseToken($token);
                    $i += 3;
                }

                $tokens []= $token;
            } elseif ($input[$i] === '[') {
                // start of optional block found, search end token `]`
                for ($start = $i++; isset($input[$i]) && $input[$i] !== ']'; ++$i);

                // no end token found, syntax error
                if (!isset($input[$i])) {
                    throw new InvalidArgumentException('Missing end of optional block');
                }

                $word = substr($input, $start + 1, $i++ - $start - 1);

                $ellipse = false;
                // ends with `...` means that any number of arguments are accepted
                if (substr($word, -3) === '...') {
                    $word = substr($word, 0, -3);
                    $ellipse = true;
                }

                // for now, the optional block may only contain arguments
                if (substr($word, 0, 1) !== '<' || substr($word, -1) !== '>') {
                    throw new InvalidArgumentException('Optional block must contain argument block');
                }

                $token = new ArgumentToken(substr($word, 1, -1));

                if ($ellipse) {
                    $token = new EllipseToken($token);
                }

                $tokens []= new OptionalToken($token);
            } else {
                // static word token, buffer until next whitespace
                for($start = $i++; isset($input[$i]) && !in_array($input[$i], $ws); ++$i);

                $word = substr($input, $start, $i - $start);

                $tokens []= new WordToken($word);
            }
        }

        // return a single token as-is
        if (isset($tokens[0]) && !isset($tokens[1])) {
            return $tokens[0];
        }

        // otherwise wrap in a sentence-token
        return new SentenceToken($tokens);
    }
}
