<?php

namespace Clue\Tests\Commander\Tokens;

use Clue\Commander\Tokens\SentenceToken;
use Clue\Commander\Tokens\WordToken;
use Clue\Tests\Commander\TestCase;

class SentenceTokenTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
    public function testSupportsAnyTwoTokens()
    {
        new SentenceToken(array(
            $this->getMockBuilder('Clue\Commander\Tokens\TokenInterface')->getMock(),
            $this->getMockBuilder('Clue\Commander\Tokens\TokenInterface')->getMock(),
        ));
    }

    public function testRequiresValidTokens()
    {
        $this->setExpectedException('InvalidArgumentException');
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
