<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$users = Illuminate\Support\Facades\Schema::getColumnListing('users');
$peserta = Illuminate\Support\Facades\Schema::getColumnListing('peserta_jadwal');

file_put_contents('/tmp/columns.json', json_encode([
    'users' => $users,
    'peserta' => $peserta
], JSON_PRETTY_PRINT));
echo "Done\n";
