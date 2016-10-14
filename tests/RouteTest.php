<?php

use Clue\Commander\Route;

class RouteTest extends PHPUnit_Framework_TestCase
{
    public function testToStringWillReturnStringFromToken()
    {
        $token = $this->getMock('Clue\Commander\Tokens\TokenInterface');
        $token->expects($this->once())->method('__toString')->willReturn('test');

        $route = new Route($token, 'var_dump');

        $this->assertEquals('test', (string)$route);
    }
}
