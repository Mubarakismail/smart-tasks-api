<?php

namespace Modules\Task\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;
use Modules\Task\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'To Do', 'code' => 'TODO', 'order' => 1, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'In Progress', 'code' => 'INPR', 'order' => 2, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Done', 'code' => 'DONE', 'order' => 3, 'created_at' => now(), 'updated_at' => now()],
        ];
        foreach ($statuses as $item) {
            Status::firstOrCreate(['code' => $item['code']], $item);
        }
    }
}
