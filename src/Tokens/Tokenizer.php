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
    /** whitespace characters to ignore */
    private $ws = array(
        ' ',
        "\t",
        "\r",
        "\n",
    );

    /**
     * Creates a Token from the given route expression
     *
     * @param string $input
     * @return TokenInterface
     * @throws InvalidArgumentException if the route expression can not be parsed
     */
    public function createToken($input)
    {
        $i = 0;
        $token = $this->readSentenceOrSingle($input, $i);

        if (isset($input[$i])) {
            throw new \InvalidArgumentException('Invalid root token, expression has superfluous contents');
        }

        return $token;
    }

    private function readSentenceOrSingle($input, &$i)
    {
        $tokens = array();

        while (true) {
            $previous = $i;
            $this->consumeOptionalWhitespace($input, $i);

            // end of input reached
            if (!isset($input[$i]) || $input[$i] === ']') {
                break;
            }

            // cursor must be moved due to whitespace if there's another token
            if ($previous === $i && $tokens) {
                throw new InvalidArgumentException('Missing whitespace between tokens');
            }

            $tokens []= $this->readToken($input, $i);
        }

        // return a single token as-is
        if (isset($tokens[0]) && !isset($tokens[1])) {
            return $tokens[0];
        }

        // otherwise wrap in a sentence-token
        return new SentenceToken($tokens);
    }

    private function consumeOptionalWhitespace($input, &$i)
    {
        // skip whitespace characters
        for (;isset($input[$i]) && in_array($input[$i], $this->ws); ++$i);
    }

    private function readToken($input, &$i)
    {
        if ($input[$i] === '<') {
            return $this->readArgument($input, $i);
        } elseif ($input[$i] === '[') {
            return $this->readOptionalBlock($input, $i);
        } else {
            return $this->readWord($input, $i);
        }
    }

    private function readArgument($input, &$i)
    {
        // start of argument found, search end token `>`
        for ($start = $i++; isset($input[$i]) && $input[$i] !== '>'; ++$i);

        // no end token found, syntax error
        if (!isset($input[$i])) {
            throw new InvalidArgumentException('Missing end of argument');
        }

        // everything between `<` and `>` is the argument name
        $word = substr($input, $start + 1, $i++ - $start - 1);
        $token = new ArgumentToken(trim($word));

        // skip any whitespace characters between end of block and `...`
        $this->consumeOptionalWhitespace($input, $i);

        // followed by `...` means that any number of arguments are accepted
        if (substr($input, $i, 3) === '...') {
            $token = new EllipseToken($token);
            $i += 3;
        }

        return $token;
    }

    private function readOptionalBlock($input, &$i)
    {
        // advance to contents of optional block and read inner sentence
        $i++;
        $token = $this->readSentenceOrSingle($input, $i);

        // above should stop at end token, otherwise syntax error
        if (!isset($input[$i])) {
            throw new InvalidArgumentException('Missing end of optional block');
        }

        // skip end token
        $i++;

        return new OptionalToken($token);
    }

    private function readWord($input, &$i)
    {
        // static word token, buffer until next whitespace or closing square bracket
        preg_match('/(?:[^\[\]\s]+|\[[^\]]+\])+/', $input, $matches, 0, $i);

        $word = $matches[0];
        $i += strlen($word);

        $ellipse = false;
        // ends with `...` means that any number of arguments are accepted
        if (substr($word, -3) === '...') {
            $word = substr($word, 0, -3);
            $ellipse = true;
        }

        if (isset($word[0]) && $word[0] === '-') {
            $required = true;
            $placeholder = null;

            // placeholder value is optional => remove square brackets
            if (substr($word, -1) === ']') {
                $required = false;
                $word = trim(str_replace(array('[', ']'), '', $word));
            }

            // value is present => remove placeholder name from word
            $pos = strpos($word, '=');
            if ($pos !== false) {
                $placeholder = trim(substr($word, $pos + 1), '<>');
                $word = substr($word, 0, $pos);
            } else {
                $required = false;
            }

            $token = new OptionToken($word, $placeholder, $required);
        } else{
            $token = new WordToken($word);
        }

        if ($ellipse) {
            $token = new EllipseToken($token);
        }

        return $token;
    }
}
