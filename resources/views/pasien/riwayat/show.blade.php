<x-layouts.app title="Detail Pemeriksaan">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Detail Pemeriksaan</h1>
        <p class="mt-1 text-slate-500">Rincian pemeriksaan dan biaya dari kunjungan Anda.</p>
    </div>
    @php($periksa = $daftarPoli->periksa)
    <section class="panel-card max-w-3xl overflow-hidden">
        <div class="grid gap-4 border-b border-slate-200 bg-slate-50/80 p-6 sm:grid-cols-3">
            <div><p class="text-xs uppercase tracking-wide text-slate-500">Poli</p><p class="mt-1 font-semibold text-slate-800">{{ $daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-slate-500">Dokter</p><p class="mt-1 font-semibold text-slate-800">{{ $daftarPoli->jadwalPeriksa->dokter->nama }}</p></div>
            <div><p class="text-xs uppercase tracking-wide text-slate-500">Tanggal Pemeriksaan</p><p class="mt-1 font-semibold text-slate-800">{{ $periksa->tgl_periksa->format('d-m-Y H:i') }}</p></div>
        </div>
        <div class="space-y-6 p-6">
            <div>
                <h2 class="font-bold text-slate-800">Catatan Dokter</h2>
                <p class="mt-2 whitespace-pre-line text-slate-600">{{ $periksa->catatan }}</p>
            </div>
            <div>
                <h2 class="font-bold text-slate-800">Daftar Obat</h2>
                <ul class="mt-2 divide-y divide-slate-100">
                    @forelse($periksa->detailPeriksas as $detail)
                        <li class="flex items-center justify-between gap-4 py-3">
                            <span class="text-slate-700">{{ $detail->obat->nama_obat }}</span>
                            <span class="font-medium text-slate-600">Rp {{ number_format($detail->obat->harga, 0, ',', '.') }}</span>
                        </li>
                    @empty
                        <li class="py-3 text-slate-500">Tidak ada obat.</li>
                    @endforelse
                </ul>
            </div>
            <div class="flex flex-col gap-4 border-t border-slate-200 pt-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-lg font-bold text-slate-800">Total biaya: <span class="text-indigo-700">Rp {{ number_format($periksa->biaya_periksa + $periksa->detailPeriksas->sum(fn ($detail) => $detail->obat->harga), 0, ',', '.') }}</span></p>
                <a href="{{ route('pasien.riwayat.index') }}" class="btn rounded-xl border-slate-200 bg-white text-slate-700 hover:bg-slate-50">Kembali</a>
            </div>
        </div>
    </section>
</x-layouts.app>
