<?php

// Opt-in browser fixture. This guard runs before any migration or fixture write.
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$expected = str_replace('\\', '/', storage_path('framework/fusion-e2e.sqlite'));
if (config('database.default') !== 'sqlite' || str_replace('\\', '/', config('database.connections.sqlite.database')) !== $expected || filled(config('database.connections.sqlite.url'))) {
    throw new RuntimeException('Only the isolated fusion-e2e.sqlite database is allowed.');
}
if (! is_file($expected)) touch($expected);
if (Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]) !== 0) throw new RuntimeException('Isolated migration failed.');
if (! App\Models\User::where('email', 'orientation@example.test')->exists()) Tests\OrientationFixture::create();
echo "ISOLATED UI FIXTURE READY\n";
