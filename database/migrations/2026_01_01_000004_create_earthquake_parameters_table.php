<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('earthquake_parameters', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('grid_id');
            $table->decimal('ss', 10, 4);
            $table->decimal('s1', 10, 4);
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('earthquake_parameters');
    }
};
