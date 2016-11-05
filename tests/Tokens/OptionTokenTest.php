<?php

use Clue\Commander\Tokens\OptionToken;

class OptionTokenTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testUnableToCreateOptionWithRequiredValueButNoPlaceholder()
    {
        new OptionToken('--name', null, true);
    }
}
