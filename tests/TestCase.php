<?php

namespace Tests;

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    public function createApplication(): Application
    {
        $app = parent::createApplication();

        $this->ensureTestsNeverUseProductionDatabase($app);

        return $app;
    }

    private function ensureTestsNeverUseProductionDatabase(Application $app): void
    {
        $connection = $app['config']->get('database.default');
        $database = $app['config']->get("database.connections.$connection.database");

        if ($connection === 'sqlite' && $database === ':memory:') {
            return;
        }

        throw new RuntimeException(
            'Pruebas bloqueadas: el entorno de testing debe usar SQLite en memoria. ' .
            'No se permite ejecutar tests sobre la base real del sistema veterinario.'
        );
    }
}
