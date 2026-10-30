<?php

namespace App\Http\Controllers\Pasien;

use App\Events\QueueUpdated;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\JadwalPeriksa;
use App\Models\Poli;
use App\Models\User;
use App\Services\QueueNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PasienController extends Controller
{
    public function dashboard(QueueNumber $queueNumber)
    {
        $jadwals = JadwalPeriksa::query()
            ->with(['dokter.poli'])
            ->where('status', 'Aktif')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->map(function (JadwalPeriksa $jadwal) use ($queueNumber) {
                $jadwal->nomor_dipanggil = $queueNumber->currentlyCalled($jadwal->id);

                return $jadwal;
            });

        $antrianAktif = DaftarPoli::query()
            ->with(['jadwalPeriksa.dokter.poli'])
            ->where('id_pasien', auth()->id())
            ->whereDoesntHave('periksas')
            ->latest()
            ->first();

        return view('pasien.dashboard', compact('jadwals', 'antrianAktif'));
    }

    public function createRegistration()
    {
        $jadwals = JadwalPeriksa::query()
            ->with(['dokter.poli'])
            ->where('status', 'Aktif')
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        $polis = Poli::query()
            ->orderBy('nama_poli')
            ->get();

        return view('pasien.daftar-poli.create', compact('jadwals', 'polis'));
    }

    public function storeRegistration(Request $request)
    {
        $data = $request->validate([
            'id_poli' => ['required', 'integer', 'exists:poli,id'],
            'id_jadwal' => ['required', 'integer', 'exists:jadwal_periksa,id'],
            'keluhan' => ['required', 'string', 'max:2000'],
        ]);

        $jadwalSesuaiPoli = JadwalPeriksa::query()
            ->whereKey($data['id_jadwal'])
            ->where('status', 'Aktif')
            ->whereHas('dokter', fn ($query) => $query->where('id_poli', $data['id_poli']))
            ->exists();

        if (! $jadwalSesuaiPoli) {
            return back()->withInput()->withErrors([
                'id_jadwal' => 'Pilih jadwal aktif yang sesuai dengan poli.',
            ]);
        }

        $daftarPoli = DB::transaction(function () use ($data) {
            $pasien = User::query()->lockForUpdate()->findOrFail(auth()->id());

            $masihMenunggu = DaftarPoli::query()
                ->where('id_pasien', $pasien->id)
                ->whereDoesntHave('periksas')
                ->exists();

            if ($masihMenunggu) {
                return null;
            }

            $jadwal = JadwalPeriksa::query()
                ->whereKey($data['id_jadwal'])
                ->where('status', 'Aktif')
                ->whereHas('dokter', fn ($query) => $query->where('id_poli', $data['id_poli']))
                ->lockForUpdate()
                ->firstOrFail();

            $nomorAntrian = (int) DaftarPoli::query()
                ->where('id_jadwal', $jadwal->id)
                ->lockForUpdate()
                ->max('no_antrian') + 1;

            return DaftarPoli::query()->create([
                'id_pasien' => $pasien->id,
                'id_jadwal' => $jadwal->id,
                'keluhan' => $data['keluhan'],
                'no_antrian' => $nomorAntrian,
            ]);
        });

        if (! $daftarPoli) {
            return back()->withInput()->withErrors([
                'id_jadwal' => 'Selesaikan pemeriksaan sebelumnya sebelum mendaftar kembali.',
            ]);
        }

        $jadwalId = $daftarPoli->id_jadwal;
        DB::afterCommit(fn () => QueueUpdated::dispatch(
            $jadwalId,
            app(QueueNumber::class)->currentlyCalled($jadwalId),
        ));

        return redirect()->route('pasien.dashboard')->with('success', 'Pendaftaran berhasil. Nomor antrean Anda '.$daftarPoli->no_antrian.'.');
    }

    public function history()
    {
        $pendaftaran = DaftarPoli::query()
            ->with(['jadwalPeriksa.dokter.poli', 'periksas'])
            ->where('id_pasien', auth()->id())
            ->latest()
            ->paginate(15);

        return view('pasien.riwayat.index', compact('pendaftaran'));
    }

    public function historyDetail(DaftarPoli $daftarPoli)
    {
        abort_unless((int) $daftarPoli->id_pasien === (int) auth()->id(), 404);

        $daftarPoli->load(['jadwalPeriksa.dokter.poli', 'periksa.detailPeriksas.obat']);
        abort_unless($daftarPoli->periksa, 404);

        return view('pasien.riwayat.show', compact('daftarPoli'));
    }
}
