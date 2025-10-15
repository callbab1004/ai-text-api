<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create("requests_log", function (Blueprint $table) {
            $table->id();
            $table->foreignId("api_key_id")->constrained("api_keys")->cascadeOnDelete();
            $table->string("mode", 16); // correct | translate
            $table->string("source_lang", 8)->nullable();
            $table->string("target_lang", 8)->nullable();
            $table->unsignedInteger("input_chars");
            $table->unsignedInteger("output_chars")->nullable();
            $table->string("status", 16); // success | fail
            $table->unsignedInteger("latency_ms")->nullable();
            $table->text("error_message")->nullable();
            $table->timestamps();

            $table->index(["api_key_id", "created_at"]);
            $table->index(["mode", "created_at"]);
        });
    }

    public function down(): void {
        Schema::dropIfExists("requests_log");
    }
};
