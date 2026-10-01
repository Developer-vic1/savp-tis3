<?php

namespace Tests\Unit;

use App\Support\PortableCheckConstraint;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PortableCheckConstraintTest extends TestCase
{
    public function test_isolated_sqlite_constraints_reject_invalid_insert_and_update_and_allow_null(): void
    {
        if (! extension_loaded('pdo_sqlite')) $this->markTestSkipped('Isolated SQLite required.');
        DB::statement('CREATE TABLE portable_fixture (value INTEGER, payload TEXT, sha256 TEXT)');
        PortableCheckConstraint::statement("ALTER TABLE portable_fixture ADD CONSTRAINT fixture_range CHECK (value BETWEEN 0 AND 100)");
        PortableCheckConstraint::statement("ALTER TABLE portable_fixture ADD CONSTRAINT fixture_object CHECK (jsonb_typeof(payload) = 'object')");
        PortableCheckConstraint::statement("ALTER TABLE portable_fixture ADD CONSTRAINT fixture_hash CHECK (sha256 ~ '^[a-f0-9]{64}$')");
        DB::table('portable_fixture')->insert(['value' => null, 'payload' => '{}', 'sha256' => str_repeat('a', 64)]);
        foreach ([['value' => 101], ['payload' => '[]'], ['sha256' => str_repeat('Z', 64)]] as $invalid) {
            try { DB::table('portable_fixture')->update($invalid); $this->fail('Invalid value accepted'); }
            catch (QueryException $error) { $this->assertStringContainsString('CHECK constraint failed', $error->getMessage()); }
        }
        $this->expectException(QueryException::class);
        DB::table('portable_fixture')->insert(['value' => -1]);
    }
}
