<?php

namespace App\Http\Controllers\Dokter;

use App\Events\QueueUpdated;
use App\Http\Controllers\Controller;
use App\Models\DaftarPoli;
use App\Models\DetailPeriksa;
use App\Models\JadwalPeriksa;
use App\Models\Obat;
use App\Models\Periksa;
use App\Services\QueueNumber;
use Illuminate\Broadcasting\BroadcastException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DokterController extends Controller
{
    public function dashboard(QueueNumber $queueNumber)
    {
        $jadwals = JadwalPeriksa::query()
            ->with(['dokter.poli'])
            ->where('id_dokter', auth()->id())
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get()
            ->map(function (JadwalPeriksa $jadwal) use ($queueNumber) {
                $jadwal->nomor_dipanggil = $queueNumber->currentlyCalled($jadwal->id);

                return $jadwal;
            });

        return view('dokter.dashboard', compact('jadwals'));
    }

    public function queue(QueueNumber $queueNumber)
    {
        $jadwals = JadwalPeriksa::query()
            ->with(['daftarPolis' => fn ($query) => $query
                ->with('pasien')
                ->whereDoesntHave('periksas')
                ->orderBy('no_antrian')])
            ->where('id_dokter', auth()->id())
            ->orderBy('hari')
            ->orderBy('jam_mulai')
            ->get();

        foreach ($jadwals as $jadwal) {
            $jadwal->nomor_dipanggil = $queueNumber->currentlyCalled($jadwal->id);
        }

        return view('dokter.antrian.index', compact('jadwals'));
    }

    public function history()
    {
        $pemeriksaans = Periksa::query()
            ->with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli'])
            ->whereHas('daftarPoli.jadwalPeriksa', fn ($query) => $query->where('id_dokter', auth()->id()))
            ->latest('tgl_periksa')
            ->paginate(15);

        return view('dokter.riwayat.index', compact('pemeriksaans'));
    }

    public function createExamination(DaftarPoli $daftarPoli)
    {
        $this->authorizeRegistration($daftarPoli);
        abort_if($daftarPoli->periksas()->exists(), 404);

        $daftarPoli->load(['pasien', 'jadwalPeriksa.dokter.poli']);
        $obats = Obat::query()->orderBy('nama_obat')->get();

        return view('dokter.periksa.create', compact('daftarPoli', 'obats'));
    }

    public function storeExamination(Request $request, DaftarPoli $daftarPoli, QueueNumber $queueNumber)
    {
        $this->authorizeRegistration($daftarPoli);
        abort_if($daftarPoli->periksas()->exists(), 404);

        $data = $request->validate([
            'catatan' => ['required', 'string', 'max:5000'],
            'biaya_periksa' => ['required', 'integer', 'min:0'],
            'obat' => ['nullable', 'array'],
            'obat.*' => ['integer', 'distinct', 'exists:obat,id'],
        ]);

        $jadwalId = $daftarPoli->id_jadwal;

        DB::transaction(function () use ($daftarPoli, $data) {
            DaftarPoli::query()->whereKey($daftarPoli->id)->lockForUpdate()->firstOrFail();

            if ($daftarPoli->periksas()->exists()) {
                abort(409, 'Pendaftaran ini sudah diperiksa.');
            }

            $periksa = Periksa::query()->create([
                'id_daftar_poli' => $daftarPoli->id,
                'tgl_periksa' => now(),
                'catatan' => $data['catatan'],
                'biaya_periksa' => $data['biaya_periksa'],
                'status_pembayaran' => 'belum_bayar',
            ]);

            foreach ($data['obat'] ?? [] as $obatId) {
                DetailPeriksa::query()->create([
                    'id_periksa' => $periksa->id,
                    'id_obat' => $obatId,
                ]);
            }
        });

        DB::afterCommit(function () use ($jadwalId, $queueNumber, $daftarPoli) {
            try {
                QueueUpdated::dispatch(
                    $jadwalId,
                    $queueNumber->currentlyCalled($jadwalId),
                    $daftarPoli->no_antrian,
                );
            } catch (BroadcastException) {
                Log::warning('Realtime queue update broadcast failed after examination was saved.', [
                    'jadwal_id' => $jadwalId,
                ]);
            }
        });

        return redirect()->route('dokter.antrian.index')->with('success', 'Pemeriksaan berhasil disimpan.');
    }

    private function authorizeRegistration(DaftarPoli $daftarPoli): void
    {
        abort_unless(
            $daftarPoli->jadwalPeriksa()
                ->where('id_dokter', auth()->id())
                ->exists(),
            404,
        );
    }
}
