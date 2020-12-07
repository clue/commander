<?php

namespace Clue\Tests\Commander;

use Clue\Commander\Route;

class RouteTest extends TestCase
{
    public function testToStringWillReturnStringFromToken()
    {
        $token = $this->getMockBuilder('Clue\Commander\Tokens\TokenInterface')->getMock();
        $token->expects($this->once())->method('__toString')->willReturn('test');

        $route = new Route($token, 'var_dump');

        $this->assertEquals('test', (string)$route);
    }
}
