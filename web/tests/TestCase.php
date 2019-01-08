<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUpTraits()
    {
        $dbPath = config("database.connections.sqlite.database");
        if ($dbPath) {
            touch($dbPath);
        }

        $this->app->make("db")
            ->getSchemaBuilder()
            ->enableForeignKeyConstraints();
        $uses = parent::setUpTraits();
        if (isset($uses[ClearMedia::class])) {
            $this->clearMedia();
        }
        if (isset($uses[ClearKeys::class])) {
            $this->clearKeys();
        }
        return $uses;
    }
}
