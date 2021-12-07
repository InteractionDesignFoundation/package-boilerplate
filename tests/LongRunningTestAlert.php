<?php

declare(strict_types=1);

namespace Tests;

use PHPUnit\Runner\AfterTestHook;

/** @see https://www.aaronsaray.com/2021/finding-slow-tests-in-phpunit-9 */
final class LongRunningTestAlert implements AfterTestHook
{
    private const MAX_SECONDS_ALLOWED = 3;

    /** @inheritDoc */
    public function executeAfterTest(string $test, float $time): void
    {
        if ($time > self::MAX_SECONDS_ALLOWED) {
            fwrite(\STDERR, sprintf("\nThe %s test took %s seconds!\n", $test, $time));
        }
    }
}
