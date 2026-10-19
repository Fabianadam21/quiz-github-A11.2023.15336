<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('jadwal_periksa', 'status')) {
            Schema::table('jadwal_periksa', function (Blueprint $table) {
                $table->enum('status', ['Aktif', 'Tidak Aktif'])->default('Aktif');
            });
        }
    }

    
    public function down(): void
    {
        if (Schema::hasColumn('jadwal_periksa', 'status')) {
            Schema::table('jadwal_periksa', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
