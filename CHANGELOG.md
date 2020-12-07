# Changelog

## 1.4.0 (2020-12-07)

*   Improve test suite and add `.gitattributes` to exclude dev files from exports.
    Add PHP 8 support, update to PHPUnit 9 and simplify test setup.
    (#26 by @andreybolonin, #27 and #28 by @SimonFrings and #29 by @clue)

## 1.3.0 (2017-08-11)

*   Feature: Add support for custom filter callbacks
    (#25 by @clue)

    ```php
    $tokenizer = new Tokenizer();
    $tokenizer->addFilter('ip', function ($value) {
        return filter_var($ip, FILTER_VALIDATE_IP);
    });
    $tokenizer->addFilter('lower', function (&$value) {
        $value = strtolower($value);
        return true;
    });

    $router = new Router($tokenizer);
    $router->add('add <name:lower>', function ($args) { });
    $router->add('--search=<address:ip>', function ($args) { });
    ```

*   Improve test suite by locking Travis distro so new future defaults will not break the build
    (#24 by @clue)

## 1.2.2 (2017-06-29)

* Fix: Assume argv to be empty if not present (non-CLI SAPI mode)
  (#23 by @clue)

* Improve test suite by adding PHPUnit to require-dev and ignoring HHVM build errors for now.
  (#21 and #22 by @clue)

## 1.2.1 (2016-11-14)

* Fix: Use casted filter value for options with boolean values
  (#20 by @clue)

## 1.2.0 (2016-11-07)

* Feature: Add support for predefined filters to limit accepted values and avoid requiring double dash separator
  (#19 by @clue)

* Feature: Support preset option values, option values now accept any tokens
  (#17 by @clue)

* Feature: Unify handling ellipses after any token and whitespace around option values
  (#17 by @clue)

## 1.1.0 (2016-11-06)

* Feature: Support alternative groups and optional parentheses
  (#15 by @clue)

* Fix: Fix multiple arguments, only skip whitespace inbetweeen once
  (#16 by @clue)

## 1.0.0 (2016-11-05)

* First stable release, now following SemVer

* Improve documentation and usage examples

> Contains no other changes, so it's actually fully compatible with the v0.2.0 release.

## 0.2.0 (2016-11-05)

* Feature / BC break: Add support for long and short options with or without option values
  (#8, #11,# 12 by @clue)

* Feature: More flexible recursive parser with support for optional keywords and required attributes
  (#9, #10 by @clue)

## 0.1.0 (2016-10-14)

* First tagged release
