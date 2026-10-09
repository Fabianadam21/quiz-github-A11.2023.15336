<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            throw new RuntimeException('The users table must exist before its clinic columns can be added.');
        }

        if (! Schema::hasColumn('users', 'nama')) {
            if (Schema::hasColumn('users', 'name')) {
                Schema::table('users', function (Blueprint $table) use (
                    $addAlamat,
                    $addNoKtp,
                    $addNoHp,
                    $addNoRm,
                    $addRole,
                ) {
                    $table->renameColumn('name', 'nama');
                });
            } else {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('nama')->nullable();
                });
            }
        }

        $addAlamat = ! Schema::hasColumn('users', 'alamat');
        $addNoKtp = ! Schema::hasColumn('users', 'no_ktp');
        $addNoHp = ! Schema::hasColumn('users', 'no_hp');
        $addNoRm = ! Schema::hasColumn('users', 'no_rm');
        $addRole = ! Schema::hasColumn('users', 'role');

        if (! ($addAlamat || $addNoKtp || $addNoHp || $addNoRm || $addRole)) {
            return;
        }

        Schema::table('users', function (Blueprint $table) {
            if ($addAlamat) {
                $table->string('alamat')->nullable();
            }

            if ($addNoKtp) {
                $table->string('no_ktp')->nullable();
            }

            if ($addNoHp) {
                $table->string('no_hp')->nullable();
            }

            if ($addNoRm) {
                $table->string('no_rm', 25)->nullable();
            }

            if ($addRole) {
                $table->enum('role', ['admin', 'dokter', 'pasien'])->default('pasien');
            }
        });
    }

    public function down(): void
    {
        // Keep the compatibility changes to avoid losing existing user data.
    }
};
