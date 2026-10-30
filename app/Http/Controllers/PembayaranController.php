<?php

namespace App\Http\Controllers;

use App\Models\Periksa;
use App\Notifications\PaymentProofUploaded;
use App\Notifications\PaymentVerified;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PembayaranController extends Controller
{
    public function patientIndex()
    {
        $pemeriksaans = Periksa::query()
            ->with(['daftarPoli.jadwalPeriksa.dokter.poli', 'detailPeriksas.obat'])
            ->whereHas('daftarPoli', fn ($query) => $query->where('id_pasien', auth()->id()))
            ->latest('tgl_periksa')
            ->paginate(15);

        return view('pasien.pembayaran.index', compact('pemeriksaans'));
    }

    public function uploadProof(Request $request, Periksa $periksa)
    {
        abort_unless(
            $periksa->daftarPoli()->where('id_pasien', auth()->id())->exists(),
            404,
        );

        $data = $request->validate([
            'bukti_pembayaran' => ['required', 'image', 'mimes:jpg,jpeg,png', 'max:5120'],
        ]);

        $path = $data['bukti_pembayaran']->store('bukti-pembayaran', 'local');
        $dokter = null;

        try {
            DB::transaction(function () use ($periksa, $path, &$dokter) {
                $lockedPeriksa = Periksa::query()->lockForUpdate()->findOrFail($periksa->id);

                if ($lockedPeriksa->status_pembayaran !== 'belum_bayar') {
                    abort(409, 'Bukti hanya dapat diunggah untuk tagihan yang belum dikirim untuk verifikasi.');
                }

                $lockedPeriksa->update([
                    'bukti_pembayaran' => $path,
                    'status_pembayaran' => 'menunggu_verifikasi',
                ]);

                $dokter = $lockedPeriksa->daftarPoli->jadwalPeriksa->dokter;
            });
        } catch (\Throwable $exception) {
            Storage::disk('local')->delete($path);
            throw $exception;
        }

        $periksa->refresh()->load('daftarPoli.pasien');
        $dokter->notify(new PaymentProofUploaded($periksa));

        return back()->with('success', 'Bukti pembayaran berhasil diunggah dan menunggu verifikasi.');
    }

    public function adminIndex()
    {
        $pemeriksaans = Periksa::query()
            ->with(['daftarPoli.pasien', 'daftarPoli.jadwalPeriksa.dokter.poli'])
            ->where('status_pembayaran', 'menunggu_verifikasi')
            ->latest('updated_at')
            ->paginate(15);

        return view('admin.pembayaran.index', compact('pemeriksaans'));
    }

    public function showProof(Periksa $periksa)
    {
        abort_unless($periksa->bukti_pembayaran, 404);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($periksa->bukti_pembayaran), 404);

        return response()->file($disk->path($periksa->bukti_pembayaran));
    }

    public function verify(Periksa $periksa)
    {
        $pasien = DB::transaction(function () use ($periksa) {
            $lockedPeriksa = Periksa::query()->lockForUpdate()->findOrFail($periksa->id);
            abort_unless(
                $lockedPeriksa->status_pembayaran === 'menunggu_verifikasi'
                    && $lockedPeriksa->bukti_pembayaran,
                409,
                'Tagihan ini tidak menunggu verifikasi.',
            );

            $lockedPeriksa->update(['status_pembayaran' => 'lunas']);

            return $lockedPeriksa->daftarPoli->pasien;
        });

        $periksa->refresh();
        $pasien->notify(new PaymentVerified($periksa));

        return back()->with('success', 'Pembayaran telah diverifikasi dan dinyatakan lunas.');
    }
}
