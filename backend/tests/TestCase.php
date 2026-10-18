<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite') && extension_loaded('pdo_mysql')) {
            $_ENV['DB_CONNECTION'] = 'mysql';
            $_SERVER['DB_CONNECTION'] = 'mysql';
            putenv('DB_CONNECTION=mysql');

            $_ENV['DB_DATABASE'] = 'uts-kepl';
            $_SERVER['DB_DATABASE'] = 'uts-kepl';
            putenv('DB_DATABASE=uts-kepl');
        }

        parent::setUp();
    }
}
