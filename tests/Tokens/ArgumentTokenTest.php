<?php

namespace Clue\Tests\Commander\Tokens;

use Clue\Commander\Tokens\ArgumentToken;
use Clue\Tests\Commander\TestCase;

class ArgumentTokenTest extends TestCase
{
    public function testCtorThrowsWithUnknownFilter()
    {
        $this->setExpectedException('InvalidArgumentException');
        new ArgumentToken('name', 'unknown');
    }

    public function testCtorThrowsWithInvalidCallable()
    {
        $this->setExpectedException('InvalidArgumentException');
        new ArgumentToken('name', 'filter', 'nope');
    }

    public function testCtorThrowsWithoutFilterButWithCallable()
    {
        $this->setExpectedException('InvalidArgumentException');
        new ArgumentToken('name', null, function () { });
    }

    public function testCtorDoesNotCallCallback()
    {
        $called = 0;
        new ArgumentToken('name', 'int', function () use (&$called) {
            ++$called;
        });

        $this->assertEquals(0, $called);
    }

    public function testMatchCallsCustomCallbackWithInputReturnsFalseDoesNotModifyInput()
    {
        $called = null;
        $token = new ArgumentToken('name', 'int', function (&$value) use (&$called) {
            $called = $value;
            $value = strtoupper($value);
            return false;
        });

        $input = array('hello');
        $output = array();
        $ret = $token->matches($input, $output);

        $this->assertFalse($ret);
        $this->assertEquals('hello', $called);
        $this->assertEquals(array('hello'), $input);
        $this->assertEquals(array(), $output);
    }

    public function testMatchCallsCustomCallbackWithInputReturnsTrueDoesModifyOutput()
    {
        $called = null;
        $token = new ArgumentToken('name', 'int', function (&$value) use (&$called) {
            $called = $value;
            $value = strtoupper($value);
            return true;
        });

        $input = array('hello');
        $output = array();
        $ret = $token->matches($input, $output);

        $this->assertTrue($ret);
        $this->assertEquals('hello', $called);
        $this->assertEquals(array(), $input);
        $this->assertEquals(array('name' => 'HELLO'), $output);
    }
}
