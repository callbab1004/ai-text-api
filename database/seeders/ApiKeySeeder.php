<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ApiKeySeeder extends Seeder
{
    public function run(): void
    {
        DB::table("api_keys")->updateOrInsert(
            ["key" => "test_dev_key_1234567890"],
            [
                "memo" => "local dev key",
                "is_active" => true,
                "last_used_at" => null,
                "created_at" => now(),
                "updated_at" => now()
            ]
        );
    }
}
