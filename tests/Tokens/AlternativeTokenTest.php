<?php

use Clue\Commander\Tokens\AlternativeToken;
use Clue\Commander\Tokens\OptionalToken;

class AlternativeTokenTest extends PHPUnit_Framework_TestCase
{
    public function testSupportsAnyTwoTokens()
    {
        new AlternativeToken(array(
            $this->getMock('Clue\Commander\Tokens\TokenInterface'),
            $this->getMock('Clue\Commander\Tokens\TokenInterface'),
        ));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequiresTokens()
    {
        new AlternativeToken(array());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRequiresValidTokens()
    {
        new AlternativeToken(array(
            true,
            false,
        ));
    }

    public function testDoesNotSupportOptional()
    {
        $token = $this->getMock('Clue\Commander\Tokens\TokenInterface');
        $tokens = array(
            $token,
            new OptionalToken($token)
        );

        $this->setExpectedException('InvalidArgumentException');
        new AlternativeToken($tokens);
    }
}
