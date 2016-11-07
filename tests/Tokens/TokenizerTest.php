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
            'word with multiple arguments' => array(
                'hello <firstname> <lastname>'
            ),
            'word with argument filter' => array(
                'hello <id:int>'
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
            'word with required short option' => array(
                'hello -f'
            ),
            'word with optional long option' => array(
                'hello [--upper]'
            ),
            'word with optional short option' => array(
                'hello [-f]'
            ),
            'word with required short option and optional short option' => array(
                'hello -i [-f]'
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
            'word with required long option with required value with filter' => array(
                'hello --number=<number:int>'
            ),
            'word with required long option with required word' => array(
                'hello --date=now'
            ),
            'word with required long option with required word ellipses' => array(
                'hello --date=now...'
            ),
            'word with required long option with required sentence nonsense' => array(
                'hello --date=(hello world)'
            ),
            'word with required long option with optional word' => array(
                'hello --date[=now]'
            ),
            'word with required long option with optional word ellipses' => array(
                'hello --date[=now]...'
            ),
            'word with required long option with optional sentence does not require parentheses nonsense' => array(
                'hello --date[=hello world]'
            ),
            'word with required long option with optional word ellipses does not require parentheses nonsense' => array(
                'hello --date[=now...]'
            ),
            'word with required long option with required group does require parentheses' => array(
                'hello --date=(a | b)'
            ),
            'word with required long option with optional group do not require parentheses' => array(
                'hello --date[=a | b]'
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

            'alternative words in sentence' => array(
                'hello | hallo'
            ),
            'alternative long or short options' => array(
                '--help | -h'
            ),
            'optional alternative long and short options' => array(
                '[--help | -h]'
            ),
            'alternative blocks with sentence' => array(
                'a | a b | c'
            )
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

            'empty argument block' => array(
                '<>'
            ),
            'incomplete argument block' => array(
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
            'argument with unknown filter' => array(
                '<name:unknown>'
            ),
            'long option with required value with unknown filter' => array(
                '--demo=<name:unknown>'
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
            ),
            'option with incomplete placeholder' => array(
                '--date=<a'
            ),
            'option with incomplete optional placeholder' => array(
                '--date[=<a>'
            ),

            'empty sentence in alternative block' => array(
                '()'
            ),
            'invalid end for alternative block' => array(
                '(hello]'
            ),
            'missing end for alternative block' => array(
                '(hello'
            ),
            'single alternative marker' => array(
                '|',
            ),
            'nothing after alternative marker' => array(
                'a|'
            ),
            'nothing before alternative marker' => array(
                '|b'
            ),

            'single ellipse' => array(
                '...'
            ),
            'ellipse after optional group' => array(
                '[a]...'
            ),
            'ellipse after alternative group' => array(
                '(a | b)...'
            ),
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

    public function testWordEllipseWithWhitespace()
    {
        $tokens = $this->tokenizer->createToken("  hello  ...    ");

        $this->assertEquals('hello...', $tokens);
    }

    public function testWordWithOptionalEllipseArgumentsWithWhitespace()
    {
        $tokens = $this->tokenizer->createToken("  hello  [  <  name  >  ...  ]  ");

        $this->assertEquals('hello [<name>...]', $tokens);
    }

    public function testAlternativeOptionsWithWhitespace()
    {
        $tokens = $this->tokenizer->createToken(' --help|  -h ');

        $this->assertEquals('--help | -h', $tokens);
    }

    public function testParenthesesForAlternativeRootSentenceIsOptional()
    {
        $tokens = $this->tokenizer->createToken('(hello | world)');

        $this->assertEquals('hello | world', $tokens);
    }

    public function testParenthesesForAlternativeOptionsIsOptional()
    {
        $tokens = $this->tokenizer->createToken('(--help | -h)');

        $this->assertEquals('--help | -h', $tokens);
    }


    public function testParenthesesForAlternativeOptionGroupIsOptional()
    {
        $tokens = $this->tokenizer->createToken('[(hello | world)]');

        $this->assertEquals('[hello | world]', $tokens);
    }

    public function testParenthesesForSingleWordIsOptional()
    {
        $tokens = $this->tokenizer->createToken('(hello)');

        $this->assertEquals('hello', $tokens);
    }

    public function testParenthesesForWordWithParenthesesIsOptional()
    {
        $tokens = $this->tokenizer->createToken('(((hello))...)');

        $this->assertEquals('hello...', $tokens);
    }

    public function testParenthesesAroundWordInSentenceIsOptional()
    {
        $tokens = $this->tokenizer->createToken('a (b) c');

        $this->assertEquals('a b c', $tokens);
    }

    public function testNestedParenthesesAroundWordsInSentenceAreOptional()
    {
        $tokens = $this->tokenizer->createToken('(((a) b) (c (d)))');

        $this->assertEquals('a b c d', $tokens);
    }

    public function testNestedAlternativeBlockWithAlternativeBlockCanBeMerged()
    {
        $tokens = $this->tokenizer->createToken('a | (b | c) | d');

        $this->assertEquals('a | b | c | d', $tokens);
    }

    public function testOptionWithRequiredWordEllipsesWithOptionalParentheses()
    {
        $tokens = $this->tokenizer->createToken('hello (--date=(now))...');

        $this->assertEquals('hello --date=now...', $tokens);
    }

    public function testOptionWithRequiredWordValueWithWhitespace()
    {
        $tokens = $this->tokenizer->createToken('--option = value ');

        $this->assertEquals('--option=value', $tokens);
    }

    public function testOptionWithOptionalWordValueWithWhitespace()
    {
        $tokens = $this->tokenizer->createToken('--option [ = value ] ');

        $this->assertEquals('--option[=value]', $tokens);
    }

    public function testParenthesesForSentenceInOptionalOptionValueIsOptional()
    {
        $tokens = $this->tokenizer->createToken('--option[=(hello world)]');

        $this->assertEquals('--option[=hello world]', $tokens);
    }
}
