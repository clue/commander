<?php

use Clue\Commander\Tokens\EllipseToken;

class EllipseTokenTest extends PHPUnit_Framework_TestCase
{
    public function testSupportsAnyToken()
    {
        $token = $this->getMock('Clue\Commander\Tokens\TokenInterface');
        new EllipseToken($token);
    }

    public function testDoesNotSupportNested()
    {
        $token = $this->getMock('Clue\Commander\Tokens\TokenInterface');
        $token = new EllipseToken($token);

        $this->setExpectedException('InvalidArgumentException');
        new EllipseToken($token);
    }
}
