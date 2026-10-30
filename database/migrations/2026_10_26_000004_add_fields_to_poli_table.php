<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $addNamaPoli = ! Schema::hasColumn('poli', 'nama_poli');
        $addKeterangan = ! Schema::hasColumn('poli', 'keterangan');

        if ($addNamaPoli || $addKeterangan) {
            Schema::table('poli', function (Blueprint $table) use ($addNamaPoli, $addKeterangan) {
                if ($addNamaPoli) {
                    $table->string('nama_poli')->nullable();
                }
                if ($addKeterangan) {
                    $table->text('keterangan')->nullable();
                }
            });
        }
    }

    public function down(): void
    {
        $columns = [];
        if (Schema::hasColumn('poli', 'nama_poli')) {
            $columns[] = 'nama_poli';
        }
        if (Schema::hasColumn('poli', 'keterangan')) {
            $columns[] = 'keterangan';
        }
        if ($columns !== []) {
            Schema::table('poli', fn (Blueprint $table) => $table->dropColumn($columns));
        }
    }
};
