<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_classes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('site_class', 10);
            $table->text('description')->nullable();
            $table->decimal('vs30_min', 10, 2)->nullable();
            $table->decimal('vs30_max', 10, 2)->nullable();
            $table->jsonb('metadata')->default('{}');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_classes');
    }
};
