<?php

namespace App\Http\Controllers;

use App\Exports\TableExport;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ExportController extends Controller
{
    public function dokter(): BinaryFileResponse
    {
        $rows = User::query()->where('role', 'dokter')->with('poli')->orderBy('nama')->get()
            ->map(fn (User $dokter) => [
                $dokter->nama,
                $dokter->email,
                $dokter->no_ktp,
                $dokter->no_hp,
                $dokter->poli?->nama_poli,
            ])->all();

        return Excel::download(new TableExport(
            ['Nama Dokter', 'Email', 'No. KTP', 'No. HP', 'Poli'],
            $rows,
        ), 'data-dokter.xlsx');
    }

    public function pasien(): BinaryFileResponse
    {
        $rows = User::query()->where('role', 'pasien')->orderBy('nama')->get()
            ->map(fn (User $pasien) => [
                $pasien->nama,
                $pasien->email,
                $pasien->no_ktp,
                $pasien->no_hp,
                $pasien->alamat,
                $pasien->no_rm,
            ])->all();

        return Excel::download(new TableExport(
            ['Nama Pasien', 'Email', 'No. KTP', 'No. HP', 'Alamat', 'No. Rekam Medis'],
            $rows,
        ), 'data-pasien.xlsx');
    }

    public function obat(): BinaryFileResponse
    {
        $rows = Obat::query()->orderBy('nama_obat')->get()
            ->map(fn (Obat $obat) => [
                $obat->nama_obat,
                $obat->kemasan,
                $obat->stok,
                $obat->harga,
            ])->all();

        return Excel::download(new TableExport(
            ['Nama Obat', 'Kemasan', 'Stok', 'Harga'],
            $rows,
        ), 'data-obat.xlsx');
    }

    public function jadwalDokter(): BinaryFileResponse
    {
        $rows = JadwalPeriksa::query()
            ->where('id_dokter', auth()->id())
            ->with('dokter.poli')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->map(fn (JadwalPeriksa $jadwal) => [
                $jadwal->hari,
                substr($jadwal->jam_mulai, 0, 5),
                substr($jadwal->jam_selesai, 0, 5),
                $jadwal->status,
                $jadwal->dokter->poli?->nama_poli,
            ])->all();

        return Excel::download(new TableExport(
            ['Hari', 'Jam Mulai', 'Jam Selesai', 'Status', 'Poli'],
            $rows,
        ), 'jadwal-saya.xlsx');
    }

    public function riwayatDokter(): BinaryFileResponse
    {
        $rows = Periksa::query()
            ->whereHas('daftarPoli.jadwalPeriksa', fn ($query) => $query->where('id_dokter', auth()->id()))
            ->with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli'])
            ->latest('tgl_periksa')
            ->get()
            ->map(fn (Periksa $periksa) => [
                $periksa->daftarPoli->pasien->nama,
                $periksa->daftarPoli->jadwalPeriksa->dokter->poli?->nama_poli,
                $periksa->tgl_periksa?->format('Y-m-d H:i'),
                $periksa->catatan,
                $periksa->biaya_periksa,
            ])->all();

        return Excel::download(new TableExport(
            ['Nama Pasien', 'Poli', 'Tanggal Pemeriksaan', 'Catatan Dokter', 'Biaya'],
            $rows,
        ), 'riwayat-pasien-dokter.xlsx');
    }
}
