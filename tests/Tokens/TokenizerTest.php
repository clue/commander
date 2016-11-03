<?php

use Clue\Commander\Tokens\Tokenizer;

class TokenizerTest extends PHPUnit_Framework_TestCase
{
    private $tokenizer;

    public function setUp()
    {
        $this->tokenizer = new Tokenizer();
    }

    public function provideValidTokens()
    {
        return array(
            'single word' => array(
                'hello'
            ),
            'sentence with multiple words' => array(
                'hello world'
            ),

            'word with argument' => array(
                'hello <name>'
            ),
            'word with optional argument' => array(
                'hello [<name>]'
            ),

            'word with optional word' => array(
                'hello [world]'
            ),
            'word with optional nested sentence' => array(
                'hello [world [now]]'
            ),

            'word with required long option' => array(
                'hello --upper'
            ),
            'word with optional short option' => array(
                'hello -f'
            ),
            'word with optional long option' => array(
                'hello [--upper]'
            ),
            'word with optional short option' => array(
                'hello [-f]'
            ),
            'word with required long option with required value' => array(
                'hello --date=<when>'
            ),
            'word with required long option with optional value' => array(
                'hello --date[=<when>]'
            ),
            'word with required short option with required value' => array(
                'hello -f=<date>'
            ),
            'word with required short option with optional value' => array(
                'hello -f[=<date>]'
            ),

            'word with ellipse arguments' => array(
                'hello <names>...'
            ),
            'word with optional ellipse arguments' => array(
                'hello [<names>...]'
            ),
            'word with optional ellipse short option' => array(
                'hello [-v...]'
            ),
        );
    }

    /**
     * @dataProvider provideValidTokens
     * @param string $expression
     */
    public function testValidTokens($expression)
    {
        $tokens = $this->tokenizer->createToken($expression);

        $this->assertEquals($expression, $tokens);
    }

    public function provideInvalidTokens()
    {
        return array(
            'empty' => array(
                ''
            ),
            'whitespace only' => array(
                "\t\r\n"
            ),

            'missing whitespace between arguments' => array(
                '<first><second>'
            ),
            'missing whitespace between argument and optional word' => array(
                '<first>[word]'
            ),

            'incomplete parameter block' => array(
                '<incomplete'
            ),
            'incomplete optional argument' => array(
                '[<word>'
            ),
            'incomplete optional block in optional block' => array(
                '[[test]'
            ),
            'closing optional block without opening' => array(
                'test]'
            ),

            'empty sentence in optional block' => array(
                '[]'
            ),
            'empty word with ellipse in optional block' => array(
                '[...]'
            ),
            'optional block in optional block' => array(
                '[[test]]'
            ),

            'long option must contain at least two characters' => array(
                '--s'
            ),
            'short optiona must only be single character' => array(
                '-nope'
            ),
            'option name is missing' => array(
                '-'
            ),
            'long option with empty placeholder name' => array(
                '--date=<>'
            )
        );
    }

    /**
     * @dataProvider provideInvalidTokens
     * @expectedException InvalidArgumentException
     * @param string $expression
     */
    public function testInvalidTokens($expression)
    {
        $this->tokenizer->createToken($expression);
    }

    public function testMultipleWordsWithWhitespace()
    {
        $tokens = $this->tokenizer->createToken(" hello\tworld\r\n");

        $this->assertEquals('hello world', $tokens);
    }

    public function testWordWithOptionalEllipseArgumentsWithWhitespace()
    {
        $tokens = $this->tokenizer->createToken("  hello  [  <  name  >  ...  ]  ");

        $this->assertEquals('hello [<name>...]', $tokens);
    }
}
