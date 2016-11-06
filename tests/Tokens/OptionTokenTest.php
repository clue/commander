<?php

use Clue\Commander\Tokens\OptionToken;
use Clue\Commander\Tokens\WordToken;

class OptionTokenTest extends PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testUnableToCreateOptionWithRequiredValueButNoPlaceholder()
    {
        new OptionToken('--name', null, true);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testUnableToCreateOptionWithWordPlaceholderToken()
    {
        new OptionToken('--name', new WordToken('test'));
    }
}
