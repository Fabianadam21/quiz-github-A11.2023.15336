<?php

namespace App\Http\Controllers\Dokter;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JadwalPeriksaController extends Controller
{
    private const HARI = [
        'Senin',
        'Selasa',
        'Rabu',
        'Kamis',
        'Jumat',
        'Sabtu',
        'Minggu',
    ];

    public function index()
    {
        $jadwalPeriksas = Auth::user()
            ->jadwalPeriksas()
            ->orderByRaw("CASE hari
                WHEN 'Senin' THEN 1
                WHEN 'Selasa' THEN 2
                WHEN 'Rabu' THEN 3
                WHEN 'Kamis' THEN 4
                WHEN 'Jumat' THEN 5
                WHEN 'Sabtu' THEN 6
                WHEN 'Minggu' THEN 7
                ELSE 8
            END")
            ->orderBy('jam_mulai')
            ->get();

        return view('dokter.jadwal-periksa.index', compact('jadwalPeriksas'));
    }

    public function create()
    {
        $hari = self::HARI;

        return view('dokter.jadwal-periksa.create', compact('hari'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'hari' => ['required', 'in:'.implode(',', self::HARI)],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);

        Auth::user()->jadwalPeriksas()->create([
            ...$validated,
            'status' => 'Aktif',
        ]);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('success', 'Jadwal periksa berhasil ditambahkan.');
    }

    public function edit(string $jadwal_periksa)
    {
        $jadwalPeriksa = $this->jadwalMilikDokter($jadwal_periksa);
        $hari = self::HARI;

        return view('dokter.jadwal-periksa.edit', compact('jadwalPeriksa', 'hari'));
    }

    public function update(Request $request, string $jadwal_periksa)
    {
        $jadwalPeriksa = $this->jadwalMilikDokter($jadwal_periksa);
        $validated = $request->validate([
            'hari' => ['required', 'in:'.implode(',', self::HARI)],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);

        $jadwalPeriksa->update($validated);

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('success', 'Jadwal periksa berhasil diperbarui.');
    }

    public function destroy(string $jadwal_periksa)
    {
        $this->jadwalMilikDokter($jadwal_periksa)->delete();

        return redirect()
            ->route('jadwal-periksa.index')
            ->with('success', 'Jadwal periksa berhasil dihapus.');
    }

    private function jadwalMilikDokter(string $id): JadwalPeriksa
    {
        return Auth::user()->jadwalPeriksas()->findOrFail($id);
    }
}
