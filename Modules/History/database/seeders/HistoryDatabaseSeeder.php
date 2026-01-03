<?php

namespace Modules\History\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\History\Models\History;

final class HistoryDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        History::factory(10)->create();
    }
}
