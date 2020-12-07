<?php

namespace Clue\Tests\Commander\Tokens;

use Clue\Commander\Tokens\OptionToken;
use Clue\Commander\Tokens\WordToken;
use Clue\Tests\Commander\TestCase;

class OptionTokenTest extends TestCase
{
    public function testUnableToCreateOptionWithRequiredValueButNoPlaceholder()
    {
        $this->setExpectedException('InvalidArgumentException');
        new OptionToken('--name', null, true);
    }
}
