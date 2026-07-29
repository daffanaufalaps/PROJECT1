<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calculation_histories', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('ss', 10, 4)->nullable();
            $table->decimal('s1', 10, 4)->nullable();
            $table->decimal('fa', 10, 4)->nullable();
            $table->decimal('fv', 10, 4)->nullable();
            $table->decimal('sms', 10, 4)->nullable();
            $table->decimal('sm1', 10, 4)->nullable();
            $table->decimal('sds', 10, 4)->nullable();
            $table->decimal('sd1', 10, 4)->nullable();
            $table->decimal('pga', 10, 4)->nullable();
            $table->decimal('mmi', 4, 2)->nullable();
            $table->string('risk_category')->nullable();
            $table->string('kds')->nullable();
            $table->text('narration')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calculation_histories');
    }
};
