<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fa_factors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('site_class', 10);
            $table->decimal('ss_min', 10, 4);
            $table->decimal('ss_max', 10, 4);
            $table->decimal('fa_value', 10, 4);
            $table->timestamps();
        });

        Schema::create('fv_factors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('site_class', 10);
            $table->decimal('s1_min', 10, 4);
            $table->decimal('s1_max', 10, 4);
            $table->decimal('fv_value', 10, 4);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fv_factors');
        Schema::dropIfExists('fa_factors');
    }
};
