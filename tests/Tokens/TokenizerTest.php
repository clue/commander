<?php

use Clue\Commander\Tokens\Tokenizer;

class TokenizerTest extends PHPUnit_Framework_TestCase
{
    private $tokenizer;

    public function setUp()
    {
        $this->tokenizer = new Tokenizer();
    }

    public function testEmpty()
    {
        $this->assertEquals('', $this->tokenizer->createToken(''));
    }

    public function testWhitespaceOnly()
    {
        $this->assertEquals('', $this->tokenizer->createToken("\t\r\n"));
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

    public function testOptionalKeyword()
    {
        $tokens = $this->tokenizer->createToken("hello [world]");

        $this->assertEquals('hello [world]', $tokens);
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
}
