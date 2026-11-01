<?php

namespace App\Support;

use Throwable;

final class SafeCallable
{
    /**
     * Run a callback; on failure report and return the fallback so chrome/mutations stay up.
     *
     * @template T
     *
     * @param  callable(): T  $callback
     * @param  T  $fallback
     * @return T
     */
    public static function run(string $label, callable $callback, mixed $fallback): mixed
    {
        try {
            return $callback();
        } catch (Throwable $e) {
            report($e);

            return $fallback;
        }
    }
}
