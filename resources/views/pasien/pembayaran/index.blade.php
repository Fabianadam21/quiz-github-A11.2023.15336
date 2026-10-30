<x-layouts.app title="Pembayaran">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Pembayaran</h1>
        <p class="mt-1 text-slate-500">Lihat tagihan pemeriksaan dan kirim bukti pembayaran.</p>
    </div>

    <div class="space-y-4">
        @forelse($pemeriksaans as $periksa)
            @php($total = $periksa->biaya_periksa + $periksa->detailPeriksas->sum(fn ($detail) => $detail->obat->harga))
            <section class="panel-card overflow-hidden">
                <div class="flex flex-col gap-5 p-6 lg:flex-row lg:items-start lg:justify-between">
                    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wide text-indigo-600">{{ $periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}</p>
                            <h2 class="mt-1 text-lg font-bold text-slate-800">Tagihan Pemeriksaan</h2>
                            <p class="mt-1 text-sm text-slate-500">Dr. {{ $periksa->daftarPoli->jadwalPeriksa->dokter->nama }} · {{ $periksa->tgl_periksa->format('d-m-Y H:i') }}</p>
                            <p class="mt-4 text-sm text-slate-500">Total tagihan</p>
                            <p class="text-2xl font-extrabold text-slate-800">Rp {{ number_format($total, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <div class="flex min-w-64 flex-col gap-3">
                        <div>
                            <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Status pembayaran</p>
                            @if($periksa->status_pembayaran === 'lunas')
                                <span class="badge badge-success badge-outline">Lunas</span>
                            @elseif($periksa->status_pembayaran === 'menunggu_verifikasi')
                                <span class="badge badge-warning badge-outline">Menunggu verifikasi</span>
                            @else
                                <span class="badge badge-error badge-outline">Belum bayar</span>
                            @endif
                        </div>
                        @if($periksa->status_pembayaran === 'belum_bayar')
                            <form method="POST" enctype="multipart/form-data" action="{{ route('pasien.pembayaran.upload', $periksa) }}" class="space-y-3 rounded-xl bg-slate-50 p-4">
                                @csrf
                                <label for="bukti_pembayaran_{{ $periksa->id }}" class="block text-sm font-semibold text-slate-700">Unggah bukti pembayaran</label>
                                <input id="bukti_pembayaran_{{ $periksa->id }}" type="file" name="bukti_pembayaran" accept="image/jpeg,image/png" class="file-input file-input-bordered w-full bg-white">
                                @error('bukti_pembayaran')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
                                <button class="btn w-full rounded-xl border-0 bg-[#2d4499] text-white hover:bg-[#243d8b]">
                                    <i class="fas fa-upload"></i> Unggah Bukti
                                </button>
                            </form>
                        @elseif($periksa->status_pembayaran === 'menunggu_verifikasi')
                            <p class="rounded-xl bg-amber-50 p-4 text-sm text-amber-800">Bukti pembayaran sudah dikirim dan sedang menunggu verifikasi Admin.</p>
                        @endif
                    </div>
                </div>
            </section>
        @empty
            <div class="panel-card p-8 text-center text-slate-500">
                <i class="fas fa-receipt mb-3 text-2xl text-slate-300"></i>
                <p class="font-medium">Belum ada tagihan hasil pemeriksaan.</p>
            </div>
        @endforelse
        <div>{{ $pemeriksaans->links() }}</div>
    </div>
</x-layouts.app>
