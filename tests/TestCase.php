<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\DatabaseTruncation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function createApplication()
    {
        $app = parent::createApplication();
        // Verifica el destino ANTES de que RefreshDatabase pueda iniciar migrations.
        if ($app['config']->get('database.default') !== 'sqlite'
            || $app['config']->get('database.connections.sqlite.database') !== ':memory:'
            || filled($app['config']->get('database.connections.sqlite.url'))) {
            throw new \RuntimeException('Las pruebas requieren SQLite en memoria; se prohíbe usar una conexión institucional.');
        }
        $databaseTraits = [
            RefreshDatabase::class,
            DatabaseMigrations::class,
            DatabaseTransactions::class,
            DatabaseTruncation::class,
        ];
        if (array_intersect($databaseTraits, class_uses_recursive(static::class)) && ! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('No hay PDO SQLite disponible para ejecutar Feature sin tocar PostgreSQL institucional.');
        }

        return $app;
    }
}
