<?php

namespace Clue\Commander;

/**
 * The NoRouteFoundException will be raised by `handleArgv()` or `handleArgs()` if no matching route could be found.
 * It extends PHP's built-in `RuntimeException`.
 */
class NoRouteFoundException extends \UnderflowException
{

}
