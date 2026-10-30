<?php

namespace Database\Seeders;

use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $poliJantung = Poli::query()->firstOrCreate(
            ['nama_poli' => 'Jantung'],
            ['keterangan' => 'Layanan pemeriksaan jantung.'],
        );

        $users = [
            [
                'nama' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => Hash::make('admin'),
                'role' => 'admin'
            ],
            [
                'nama' => 'Dokter',
                'email' => 'dokter@gmail.com',
                'password' => Hash::make('dokter'),
                'role' => 'dokter',
                'id_poli' => $poliJantung->id,
            ],
            [
                'nama' => 'Pasien',
                'email' => 'pasien@gmail.com',
                'password' => Hash::make('pasien'),
                'role' => 'pasien'
            ]
        ];

        foreach ($users as $attributes) {
            User::updateOrCreate(
                ['email' => $attributes['email']],
                $attributes,
            );
        }

        $dokter = User::query()->where('email', 'dokter@gmail.com')->firstOrFail();
        $jadwalContoh = [
            ['hari' => 'Senin', 'jam_mulai' => '18:00', 'jam_selesai' => '21:00'],
            ['hari' => 'Selasa', 'jam_mulai' => '18:00', 'jam_selesai' => '21:00'],
            ['hari' => 'Rabu', 'jam_mulai' => '13:00', 'jam_selesai' => '15:00'],
        ];

        foreach ($jadwalContoh as $jadwal) {
            JadwalPeriksa::query()->firstOrCreate(
                [
                    'id_dokter' => $dokter->id,
                    ...$jadwal,
                ],
                ['status' => 'Aktif'],
            );
        }
    }
}