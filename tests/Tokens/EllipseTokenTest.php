<?php

namespace Clue\Tests\Commander\Tokens;

use Clue\Commander\Tokens\EllipseToken;
use Clue\Commander\Tokens\WordToken;
use Clue\Tests\Commander\TestCase;

class EllipseTokenTest extends TestCase
{
    /**
     * @doesNotPerformAssertions
     */
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
