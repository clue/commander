<?php

use Clue\Commander\Tokens\EllipseToken;
use Clue\Commander\Tokens\WordToken;

class EllipseTokenTest extends PHPUnit_Framework_TestCase
{
    public function testSupportsWordToken()
    {
        new EllipseToken(new WordToken('test'));
    }

    public function testDoesNotSupportNested()
    {
        $token = new EllipseToken(new WordToken('test'));

        $this->setExpectedException('InvalidArgumentException');
        new EllipseToken($token);
    }
}
