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
    public function testOptionalBlockMustContainArgument()
    {
        $this->tokenizer->createToken("hello [world]");
    }
}
