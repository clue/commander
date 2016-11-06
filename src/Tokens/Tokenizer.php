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
        $token = $this->readAlternativeSentenceOrSingle($input, $i);

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

            // end of input reached or end token found
            if (!isset($input[$i]) || strpos('])|', $input[$i]) !== false) {
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
        } elseif ($input[$i] === '(') {
            return $this->readParenthesesBlock($input, $i);
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
        $start = $i;
        $this->consumeOptionalWhitespace($input, $start);

        // followed by `...` means that any number of arguments are accepted
        if (substr($input, $start, 3) === '...') {
            $token = new EllipseToken($token);
            $i = $start + 3;
        }

        return $token;
    }

    private function readOptionalBlock($input, &$i)
    {
        // advance to contents of optional block and read inner sentence
        $i++;
        $token = $this->readAlternativeSentenceOrSingle($input, $i);

        // above should stop at end token, otherwise syntax error
        if (!isset($input[$i]) || $input[$i] !== ']') {
            throw new InvalidArgumentException('Missing end of optional block');
        }

        // skip end token
        $i++;

        return new OptionalToken($token);
    }

    private function readParenthesesBlock($input, &$i)
    {
        // advance to contents of parentheses block and read inner sentence
        $i++;
        $token = $this->readAlternativeSentenceOrSingle($input, $i);

        // above should stop and end token, otherwise syntax error
        if (!isset($input[$i]) || $input[$i] !== ')') {
            throw new InvalidArgumentException('Missing end of alternative block');
        }

        // skip end token
        $i++;

        return $token;
    }

    /**
     * reads a complete sentence token until end of group
     *
     * An "alternative sentence" may contain the following tokens:
     * - an alternative group (which may consist of individual sentences separated by `|`)
     * - a sentence (which may consist of multiple tokens)
     * - a single token
     *
     * @param string $input
     * @param int $i
     * @throws InvalidArgumentException
     * @return TokenInterface
     */
    private function readAlternativeSentenceOrSingle($input, &$i)
    {
        $tokens = array();

        while (true) {
            $tokens []= $this->readSentenceOrSingle($input, $i);

            // end of input reached or end token found
            if (!isset($input[$i]) || strpos('])', $input[$i]) !== false) {
                break;
            }

            // cursor now at alternative symbol (all other symbols are already handled)
            // skip alternative mark and continue with next alternative
            $i++;
        }

        // return a single token as-is
        if (isset($tokens[0]) && !isset($tokens[1])) {
            return $tokens[0];
        }

        return new AlternativeToken($tokens);
    }

    private function readWord($input, &$i)
    {
        // static word token, buffer until next whitespace or special char
        preg_match('/[^\[\]\(\)\|\=\.\s]+/', $input, $matches, 0, $i);

        $word = isset($matches[0]) ? $matches[0] : '';
        $i += strlen($word);

        if (isset($word[0]) && $word[0] === '-') {
            if (isset($input[$i + 2]) && $input[$i] === '[' && $input[$i + 1] === '=' && $input[$i + 2] === '<') {
                // placeholder value is optional
                // skip opening `[=`, read argument and expect closing bracket
                $i += 2;
                $placeholder = $this->readArgument($input, $i);

                if (!isset($input[$i]) || $input[$i] !== ']') {
                    throw new InvalidArgumentException('Missing end of optional option value');
                }

                // skip trailing closing bracket
                $i++;
                $required = false;
            } elseif (isset($input[$i + 1]) && $input[$i] === '=' && $input[$i + 1] === '<') {
                // placeholder value is required
                // skip one character for `=` and read until end of <argument>
                $i++;
                $placeholder = $this->readArgument($input, $i);
                $required = true;
            } else {
                $required = false;
                $placeholder = null;
            }

            $token = new OptionToken($word, $placeholder, $required);
        } else{
            $token = new WordToken($word);
        }

        // skip trailing whitespace to check for ellipses
        $start = $i;
        $this->consumeOptionalWhitespace($input, $start);

        // found `...` after some optional whitespace
        if (substr($input, $start, 3) === '...') {
            $token = new EllipseToken($token);
            $i = $start + 3;
        }

        return $token;
    }
}
