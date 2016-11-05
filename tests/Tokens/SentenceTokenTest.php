<?php

use Clue\Commander\Tokens\SentenceToken;
use Clue\Commander\Tokens\WordToken;

class SentenceTokenTest extends PHPUnit_Framework_TestCase
{
    public function testSupportsAnyTwoTokens()
    {
        new SentenceToken(array(
            $this->getMock('Clue\Commander\Tokens\TokenInterface'),
            $this->getMock('Clue\Commander\Tokens\TokenInterface'),
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequiresValidTokens()
    {
        new SentenceToken(array(
            true,
            false,
        ));
    }

    public function testSingleNestedSentenceWillBeAccepted()
    {
        $sentence = new SentenceToken(array(
            new WordToken('a'),
            new WordToken('b'),
        ));

        $sentence = new SentenceToken(array($sentence));

        $this->assertEquals('a b', $sentence);
    }

    public function testNestedSentenceWillBeMerged()
    {
        $sentence = new SentenceToken(array(
            new WordToken('a'),
            new WordToken('b'),
        ));

        $sentence = new SentenceToken(array(
            $sentence,
            $sentence,
        ));

        $this->assertEquals('a b a b', $sentence);
    }
}
