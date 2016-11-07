<?php

use Clue\Commander\Router;

class RouterTest extends PHPUnit_Framework_TestCase
{
    public function testEmptyRouterHasNoRoutes()
    {
        $router = new Router();

        $this->assertEquals(array(), $router->getRoutes());
    }

    public function testAddRouteReturnRoute()
    {
        $router = new Router();

        $route = $router->add('hello', function () { });

        $this->assertInstanceOf('Clue\Commander\Route', $route);
        $this->assertEquals(array($route), $router->getRoutes());
    }

    public static function provideMatchingRoutes()
    {
        return array(
            'single word' => array(
                'hello',
                array('hello'),
                array()
            ),
            'empty route' => array(
                '',
                array(),
                array()
            ),
            'word with argument' => array(
                'hello <name>',
                array('hello', 'clue'),
                array('name' => 'clue')
            ),
            'word with argument filtered int' => array(
                'hello <name:int>',
                array('hello', '10'),
                array('name' => 10)
            ),
            'word with argument filtered float' => array(
                'hello <name:float>',
                array('hello', '10.1'),
                array('name' => 10.1)
            ),
            'word with argument filtered bool' => array(
                'hello <name:bool>',
                array('hello', 'yes'),
                array('name' => true)
            ),
            'word with optional argument' => array(
                'hello [<name>]',
                array('hello', 'clue'),
                array('name' => 'clue')
            ),
            'word without optional argument' => array(
                'hello [<name>]',
                array('hello'),
                array()
            ),
            'word with ellipse arguments' => array(
                'hello <names>...',
                array('hello', 'first', 'second'),
                array('names' => array('first', 'second'))
            ),
            'word without optional ellipse arguments' => array(
                'hello [<names>...]',
                array('hello'),
                array()
            ),
            'word with optional long option' => array(
                'hello [--test]',
                array('hello', '--test'),
                array('test' => false)
            ),
            'word without optional long option' => array(
                'hello [--test]',
                array('hello'),
                array()
            ),
            'word without optional long option first' => array(
                '[--test] hello',
                array('hello'),
                array()
            ),
            'word with optional long option first' => array(
                '[--test] hello',
                array('hello', '--test'),
                array('test' => false)
            ),
            'word with optional short option' => array(
                'hello [-i]',
                array('hello', '-i'),
                array('i' => false)
            ),
            'word without optional short option' => array(
                'hello [-i]',
                array('hello'),
                array()
            ),
            'word with optional long and short option before words' => array(
                'hello [--test] [-i]',
                array('-i', '--test', 'hello'),
                array('test' => false, 'i' => false)
            ),
            'word with argument ignores double dash' => array(
                'hello <name>',
                array('hello', '--', 'clue'),
                array('name' => 'clue')
            ),
            'word with argument starting with dash' => array(
                'hello <name>',
                array('hello', '--', '-nobody-'),
                array('name' => '-nobody-')
            ),
            'word without optional argument ignores double dash' => array(
                'hello [<name>]',
                array('hello', '--'),
                array()
            ),

            'alternative group with words' => array(
                '(a | b | c)',
                array('b'),
                array()
            ),
            'alternative group with long and without short options' => array(
                '(--help | -h)',
                array('--help'),
                array('help' => false)
            ),
            'alternative group without long and with short options' => array(
                '(--help | -h)',
                array('-h'),
                array('h' => false)
            ),
            'optional alternative group with long and without short options' => array(
                '[--help | -h]',
                array('--help'),
                array('help' => false)
            ),
            'optional alternative group without long and with short options' => array(
                '[--help | -h]',
                array('-h'),
                array('h' => false)
            ),
            'optional alternative group without long and without short options' => array(
                '[--help | -h]',
                array(),
                array()
            ),

            'word with required long option with required value' => array(
                'hello --name=<yes>',
                array('hello', '--name=demo'),
                array('name' => 'demo'),
            ),
            'word with required long option with required value separated' => array(
                'hello --name=<yes>',
                array('hello', '--name', 'demo'),
                array('name' => 'demo'),
            ),
            'word with required long option with optional value' => array(
                'hello --name[=<yes>]',
                array('hello', '--name=demo'),
                array('name' => 'demo'),
            ),
            'word with required long option with optional value separated' => array(
                'hello --name[=<yes>]',
                array('hello', '--name', 'demo'),
                array('name' => 'demo'),
            ),
            'word with required long option witout optional value' => array(
                'hello --name[=<yes>]',
                array('hello', '--name'),
                array('name' => false),
            ),
            'word with required long option with following option instead of value' => array(
                'hello --name[=<yes>] [--yes]',
                array('hello', '--name', '--yes'),
                array('name' => false, 'yes' => false),
            ),
            'word with required long option with required filtered value' => array(
                'hello --name=<n:int>',
                array('hello', '--name=10'),
                array('name' => 10)
            ),
            'word with required long option with required filtered value separated' => array(
                'hello --name=<n:int>',
                array('hello', '--name', '10'),
                array('name' => 10)
            ),
            'word with required long option with required alternative value' => array(
                'hello --name=(a | b)',
                array('hello', '--name=a'),
                array('name' => 'a'),
            ),
            'word with required long option with required alternative value separated' => array(
                'hello --name=(a | b)',
                array('hello', '--name', 'b'),
                array('name' => 'b'),
            ),
            'word with optional long option without required alternative with value keyword' => array(
                'hello --name[=a | b] [c]',
                array('hello', '--name', 'c'),
                array('name' => false),
            ),
            'word with required long option with required alternative filtered int value' => array(
                'hello --name=(<n:int> | now)',
                array('hello', '--name=10'),
                array('name' => 10),
            ),
            'word with required long option with required alternative not filtered value' => array(
                'hello --name=(<n:int> | now)',
                array('hello', '--name=now'),
                array('name' => 'now'),
            ),
            'word with required long option with required keyword ellipses' => array(
                'hello --ask=no...',
                array('hello', '--ask=no', '--ask=no'),
                array('ask' => array('no', 'no')),
            ),

            'word with required short option with required value' => array(
                'hello -i=<n>',
                array('hello', '-i=demo'),
                array('i' => 'demo'),
            ),
            'word with required short option with required value concatenated' => array(
                'hello -i=<n>',
                array('hello', '-idemo'),
                array('i' => 'demo'),
            ),
            'word with required short option with required value separated' => array(
                'hello -i=<n>',
                array('hello', '-i', 'demo'),
                array('i' => 'demo'),
            ),
            'word with required short option with optional value' => array(
                'hello -i[=<n>]',
                array('hello', '-i=demo'),
                array('i' => 'demo'),
            ),
            'word with required short option with optional value separated' => array(
                'hello -i[=<n>]',
                array('hello', '-i', 'demo'),
                array('i' => 'demo'),
            ),
            'word with required short option without optional value' => array(
                'hello -i[=<n>]',
                array('hello', '-i'),
                array('i' => false),
            ),
            'word with required short option with following option instead of value' => array(
                'hello -i[=<n>] [-n]',
                array('hello', '-i', '-n'),
                array('i' => false, 'n' => false),
            ),
        );
    }

    /**
     * @dataProvider provideMatchingRoutes
     * @param string $route
     * @param array $args
     * @param array $expected
     */
    public function testHandleRouteMatches($route, $args, $expected)
    {
        $router = new Router();

        $invoked = null;
        $router->add($route, function ($args) use (&$invoked) {
            $invoked = $args;
        });

        $this->assertNull($invoked);

        $router->handleArgs($args);

        $this->assertSame($expected, $invoked);
    }

    public function provideNonMatchingRoutes()
    {
        return array(
            'other word' => array(
                'hello',
                array('test')
            ),
            'excesive arguments' => array(
                'hello',
                array('hello', 'world')
            ),
            'word in sentence missing' => array(
                'hello world',
                array('hello')
            ),
            'argument missing' => array(
                'hello <name>',
                array('hello')
            ),
            'argument not bool filter' => array(
                'hello <try:bool>',
                array('hello', 'test')
            ),
            'argument not ufloat filter' => array(
                'hello <try:ufloat>',
                array('hello', 'nope')
            ),
            'without ellipse arguments' => array(
                'hello <names>...',
                array('hello')
            ),
            'with keyword after ellipses never matches' => array(
                'hello <names>... test',
                array('hello', 'a', 'b', 'test')
            ),
            'without keyword after optional keyword' => array(
                'hello [user] user',
                array('hello', 'user')
            ),

            'alternative group with wrong word' => array(
                '(a | b | c)',
                array('d')
            ),
            'alternative group without any option' => array(
                '(--help | -h)',
                array()
            ),

            'without long option' => array(
                'hello [--test]',
                array('hello', 'test')
            ),
            'without short option' => array(
                'hello [-i]',
                array('hello', 'test')
            ),
            'with option instead of argument' => array(
                'hello <name>',
                array('hello', '--test')
            ),
            'with explicit dash argument instead of option' => array(
                'hello [--test]',
                array('hello', '--', '--test')
            ),
            'invalid word in sentence' => array(
                'hello word [<any>]',
                array('hello', 'not', 'word')
            ),

            'invalid long option shares same prefix' => array(
                'test --option',
                array('test', '--options')
            ),
            'value passed to long option but does not accept value' => array(
                'test --option',
                array('test', '--option=value')
            ),
            'without required long option value' => array(
                'test --option=<value>',
                array('test', '--option')
            ),
            'uses option instead of required long option value' => array(
                'test --option=<value>',
                array('test', '--option', '--value')
            ),

            'short option shares same prefix but does not accept value' => array(
                'test -i',
                array('test', '-ix')
            ),
            'without required short option value' => array(
                'test -i=<value>',
                array('test', '-i')
            ),
            'uses option instead of required short option value' => array(
                'test -i=<value>',
                array('test', '-i', '-n')
            ),

            'alternative options do not accept both' => array(
                'test [--help | -h]',
                array('test', '--help', '-h')
            ),
            'with required long option with value not in alternative group' => array(
                'hello --name=(a | b)',
                array('hello', '--name=c'),
            ),
            'with required long option with value separated not in alternative group' => array(
                'hello --name=(a | b)',
                array('hello', '--name', 'c'),
            ),
            'with required long option with value not in int filter' => array(
                'hello --name=<n:int>',
                array('hello', '--name=a')
            ),
            'with required long option with value not in int filter separated' => array(
                'hello --name=<n:int>',
                array('hello', '--name', 'a')
            ),
        );
    }

    /**
     * @dataProvider provideNonMatchingRoutes
     * @expectedException Clue\Commander\NoRouteFoundException
     * @param string $route
     * @param array  $args
     */
    public function testHandleRouteDoesNotMatch($route, $args)
    {
        $router = new Router();
        $router->add($route, 'var_dump');

        $router->handleArgs($args);
    }

    public function testAddRouteCanBeRemoved()
    {
        $router = new Router();

        $route = $router->add('hello', function () { });

        $router->remove($route);

        $this->assertEquals(array(), $router->getRoutes());
    }

    /**
     * @expectedException UnderflowException
     */
    public function testCanNotRemoveRouteWhichHasNotBeenAdded()
    {
        $router = new Router();
        $route = $router->add('hello', function () { });

        $router2 = new Router();
        $router2->remove($route);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testAddRouteThrowsForInvalidHandler()
    {
        $router = new Router();

        $router->add('hello', 'invalid');
    }

    public function testHandleArgvAddedRouteEmpty()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello', function ($args) use (&$invoked) {
            $invoked = $args;
        });

        $this->assertNull($invoked);

        $router->handleArgv(array('ignored', 'hello'));

        $this->assertEquals(array(), $invoked);
    }

    public function testHandleArgvFromGlobalsAddedRouteEmpty()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello', function ($args) use (&$invoked) {
            $invoked = $args;
        });

        $this->assertNull($invoked);

        $orig = $_SERVER['argv'];
        $_SERVER['argv'] = array('ignored', 'hello');
        $router->handleArgv();
        $_SERVER['argv'] = $orig;

        $this->assertEquals(array(), $invoked);
    }

    public function testExecArgvAddedRouteEmpty()
    {
        $router = new Router();

        $invoked = null;
        $router->add('hello', function ($args) use (&$invoked) {
            $invoked = $args;
            return 2;
        });

        $this->assertNull($invoked);

        $ret = $router->execArgv(array('ignored', 'hello'));

        $this->assertEquals(array(), $invoked);

        $this->assertNull($ret);
    }

    /**
     * @expectedException Clue\Commander\NoRouteFoundException
     */
    public function testHandleEmptyRouterThrowsUnderflowException()
    {
        $router = new Router();

        $router->handleArgs(array());
    }
}
