<?php

use Clue\Commander\Tokens\Tokenizer;

class TokenizerTest extends PHPUnit_Framework_TestCase
{
    private $tokenizer;

    public function setUp()
    {
        $this->tokenizer = new Tokenizer();
    }

    public function testSingleWord()
    {
        $tokens = $this->tokenizer->createToken("hello");

        $this->assertEquals('hello', $tokens);
    }

    public function testMultipleWordsWithWhitespace()
    {
        $tokens = $this->tokenizer->createToken(" hello\tworld\r\n");

        $this->assertEquals('hello world', $tokens);
    }

    public function testWordWithArgument()
    {
        $tokens = $this->tokenizer->createToken("hello <name>");

        $this->assertEquals('hello <name>', $tokens);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIncompleteArgument()
    {
        $this->tokenizer->createToken("<incomplete");
    }

    public function testWordWithOptionalArgument()
    {
        $tokens = $this->tokenizer->createToken("hello [<name>]");

        $this->assertEquals('hello [<name>]', $tokens);
    }

    public function testWordWithOptionalLongOption()
    {
        $tokens = $this->tokenizer->createToken("hello [--upper]");

        $this->assertEquals('hello [--upper]', $tokens);
    }

    public function testWordWithOptionalShortOption()
    {
        $tokens = $this->tokenizer->createToken("hello [-f]");

        $this->assertEquals('hello [-f]', $tokens);
    }

    public function testWordWithRequiredLongOption()
    {
        $tokens = $this->tokenizer->createToken("hello --upper");

        $this->assertEquals('hello --upper', $tokens);
    }

    public function testWordWithRequiredShortOption()
    {
        $tokens = $this->tokenizer->createToken("hello -f");

        $this->assertEquals('hello -f', $tokens);
    }

    public function testWordWithArgumentEllipses()
    {
        $tokens = $this->tokenizer->createToken("hello <name>...");

        $this->assertEquals('hello <name>...', $tokens);
    }

    public function testWordWithOptionalEllipseArguments()
    {
        $tokens = $this->tokenizer->createToken("hello [<name>...]");

        $this->assertEquals('hello [<name>...]', $tokens);
    }

    public function testWordWithOptionalEllipseShortOption()
    {
        $tokens = $this->tokenizer->createToken("hello [-v...]");

        $this->assertEquals('hello [-v...]', $tokens);
    }

    public function testWordWithOptionalEllipseArgumentsWithWhitespace()
    {
        $tokens = $this->tokenizer->createToken("  hello  [  <  name  >  ...  ]  ");

        $this->assertEquals('hello [<name>...]', $tokens);
    }

    public function testOptionalKeyword()
    {
        $tokens = $this->tokenizer->createToken("hello [world]");

        $this->assertEquals('hello [world]', $tokens);
    }

    public function testOptionalKeywordWithNestedOptionalSentence()
    {
        $tokens = $this->tokenizer->createToken("hello [world [now]]");

        $this->assertEquals('hello [world [now]]', $tokens);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptyIsNotAToken()
    {
        $this->tokenizer->createToken('');
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWhitespaceOnlyIsNotAToken()
    {
        $this->tokenizer->createToken("\t\r\n");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMissingWhitespaceBetweenArguments()
    {
        $this->tokenizer->createToken("<first><second>");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testMissingWhitespaceBetweenArgumentAndOptionalWord()
    {
        $this->tokenizer->createToken("<first>[word]");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIncompleteOptionalArgument()
    {
        $this->tokenizer->createToken("[<word>");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testClosingOptionalBlockWithoutOpening()
    {
        $this->tokenizer->createToken("test]");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptySentenceInOptionalBlock()
    {
        $this->tokenizer->createToken("[]");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testOptionalBlockInOptionalBlock()
    {
        $this->tokenizer->createToken("[[test]]");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testEmptyWordWithEllipseInOptionalBlock()
    {
        $this->tokenizer->createToken("[...]");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testIncompleteOptionalBlockInOptionalBlock()
    {
        $this->tokenizer->createToken("[[test]");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testOptionalLongOptionMustContainAtLeastTwoChars()
    {
        $this->tokenizer->createToken("hello [--s]");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testOptionalShortOptionMustOnlyBeSingleCharacter()
    {
        $this->tokenizer->createToken("hello [-nope]");
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testOptionalOptionNameIsMissing()
    {
        $this->tokenizer->createToken("hello [-]");
    }
}
