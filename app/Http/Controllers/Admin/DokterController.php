<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DokterController extends Controller
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
        $dokters = User::where('role', 'dokter')->with('poli')->get();
        return view('admin.dokter.index', compact('dokters'));
    }

    public function create()
    {
        $polis = Poli::all();
        return view('admin.dokter.create', compact('polis'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'no_ktp' => ['required', 'string', 'max:16', 'unique:users,no_ktp'],
            'no_hp' => ['required', 'string', 'max:15'],
            'id_poli' => ['required', 'integer', 'exists:poli,id'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'role' => 'dokter',
        ]);

        return redirect()->route('dokter.index')
            ->with('success', 'Data dokter berhasil ditambahkan.');
    }

    public function edit(User $dokter)
    {
        abort_unless($dokter->role === 'dokter', 404);

        $polis = Poli::all();
        return view('admin.dokter.edit', compact('dokter', 'polis'));
    }

    public function schedules(User $dokter)
    {
        abort_unless($dokter->role === 'dokter', 404);

        $jadwalPeriksas = $dokter->jadwalPeriksas()->orderBy('hari')->orderBy('jam_mulai')->get();
        $hari = self::HARI;

        return view('admin.dokter.jadwal', compact('dokter', 'jadwalPeriksas', 'hari'));
    }

    public function storeSchedule(Request $request, User $dokter)
    {
        abort_unless($dokter->role === 'dokter', 404);

        $data = $request->validate([
            'hari' => ['required', 'in:'.implode(',', self::HARI)],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
        ]);

        $dokter->jadwalPeriksas()->create([
            ...$data,
            'status' => 'Aktif',
        ]);

        return redirect()
            ->route('admin.dokter.jadwal', $dokter)
            ->with('success', 'Jadwal dokter berhasil ditambahkan.');
    }

    public function update(Request $request, User $dokter)
    {
        abort_unless($dokter->role === 'dokter', 404);

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:255'],
            'alamat' => ['required', 'string'],
            'no_ktp' => ['required', 'string', 'max:16', 'unique:users,no_ktp,'.$dokter->id],
            'no_hp' => ['required', 'string', 'max:15'],
            'id_poli' => ['required', 'integer', 'exists:poli,id'],
            'email' => ['required', 'email', 'unique:users,email,'.$dokter->id],
            'password' => ['nullable', 'string', 'min:6'],
        ]);

        $password = $data['password'] ?? null;
        unset($data['password']);

        if ($password !== null && $password !== '') {
            $data['password'] = Hash::make($password);
        }

        $dokter->update($data);

        return redirect()->route('dokter.index')
            ->with('success', 'Data dokter berhasil diperbarui.');
    }

    public function destroy(User $dokter)
    {
        $dokter->delete();

        return redirect()->route('dokter.index')
            ->with('message', 'Data Dokter Berhasil dihapus')
            ->with('type', 'success');
    }
}