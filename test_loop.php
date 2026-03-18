<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$user = \App\Models\User::find(6);
auth()->login($user);

echo "Instantiating page...\n";
$page = app()->make(\App\Filament\Pages\StatusPeserta::class);

echo "Mounting page...\n";
$page->mount();

echo "Getting form...\n";
$page->getForm('form');

echo "Getting table...\n";
$table = $page->getTable();

echo "Getting columns...\n";
$columns = $table->getColumns();

echo "Getting records...\n";
$records = $table->getRecords();
echo "Found " . count($records) . " records\n";

foreach($records as $record) {
    echo "Processing record " . $record->id . "\n";
    foreach($columns as $column) {
       echo "  Column: " . $column->getName() . "\n";
       $column->record($record);
       $val = $column->getState();
    }
}
echo "Done\n";
