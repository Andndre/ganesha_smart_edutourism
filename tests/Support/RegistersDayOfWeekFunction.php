<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use PDO\Sqlite;

trait RegistersDayOfWeekFunction
{
    protected function registerDayOfWeekFunction(): void
    {
        $callback = function (string $datetime): int {
            return (int) date('w', strtotime($datetime)) + 1;
        };

        $pdo = DB::connection()->getPdo();

        if (method_exists($pdo, 'createFunction')) {
            /** @var Sqlite $pdo */
            $pdo->createFunction('DAYOFWEEK', $callback);
        } else {
            $pdo->sqliteCreateFunction('DAYOFWEEK', $callback);
        }
    }
}
