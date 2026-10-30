<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('obat', 'stok')) {
            Schema::table('obat', function (Blueprint $table) {
                $table->unsignedInteger('stok')->default(0);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('obat', 'stok')) {
            Schema::table('obat', function (Blueprint $table) {
                $table->dropColumn('stok');
            });
        }
    }
};
