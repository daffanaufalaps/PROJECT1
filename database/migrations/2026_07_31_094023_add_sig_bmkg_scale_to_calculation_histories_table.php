<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('calculation_histories', function (Blueprint $table) {
            $table->string('sig_bmkg_scale', 5)->nullable()->after('pga');
        });
    }

    public function down(): void
    {
        Schema::table('calculation_histories', function (Blueprint $table) {
            $table->dropColumn('sig_bmkg_scale');
        });
    }
};