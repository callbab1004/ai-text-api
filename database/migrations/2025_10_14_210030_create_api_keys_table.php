<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create("api_keys", function (Blueprint $table) {
            $table->id();
            $table->string("key", 100)->unique();
            $table->string("memo")->nullable();
            $table->boolean("is_active")->default(true);
            $table->timestamp("last_used_at")->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists("api_keys");
    }
};
