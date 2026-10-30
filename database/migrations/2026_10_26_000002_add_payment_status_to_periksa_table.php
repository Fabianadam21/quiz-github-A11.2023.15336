<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('periksa', function (Blueprint $table) {
            if (! Schema::hasColumn('periksa', 'status_pembayaran')) {
                $table->string('status_pembayaran')->default('belum_bayar');
            }

            if (! Schema::hasColumn('periksa', 'bukti_pembayaran')) {
                $table->string('bukti_pembayaran')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('periksa', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('periksa', 'status_pembayaran')) {
                $columns[] = 'status_pembayaran';
            }
            if (Schema::hasColumn('periksa', 'bukti_pembayaran')) {
                $columns[] = 'bukti_pembayaran';
            }
            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
