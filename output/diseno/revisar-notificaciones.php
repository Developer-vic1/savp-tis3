<?php
require __DIR__.'/../../vendor/autoload.php';
$app = require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$exists = Illuminate\Support\Facades\Schema::hasTable('notifications');
echo json_encode(['tabla'=>$exists,'habilitado'=>config('features.notifications'),'columnas'=>$exists ? Illuminate\Support\Facades\Schema::getColumnListing('notifications') : [],'cantidad'=>$exists ? Illuminate\Support\Facades\DB::table('notifications')->count() : null]);
