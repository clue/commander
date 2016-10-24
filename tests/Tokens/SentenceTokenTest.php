<?php

use Clue\Commander\Tokens\SentenceToken;

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

    public function testDoesNotSupportNested()
    {
        $sentence = new SentenceToken(array(
            $this->getMock('Clue\Commander\Tokens\TokenInterface'),
            $this->getMock('Clue\Commander\Tokens\TokenInterface'),
        ));

        $this->setExpectedException('InvalidArgumentException');
        new SentenceToken(array($sentence, $sentence));
    }
}
