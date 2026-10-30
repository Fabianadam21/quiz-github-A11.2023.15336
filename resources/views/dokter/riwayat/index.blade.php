<x-layouts.app title="Riwayat Pasien">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Riwayat Pasien</h1>
        <a href="{{ route('dokter.export.riwayat') }}" class="inline-flex h-10 w-36 items-center justify-center gap-2 border border-primary px-3 text-sm font-semibold text-primary rounded-xl transition hover:bg-primary hover:text-white">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
    </div>
    <div class="card bg-base-100 shadow-sm rounded-2xl border">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr><th>Pasien</th><th>Poli</th><th>Tanggal Periksa</th><th>Catatan</th><th>Biaya</th></tr>
                </thead>
                <tbody>
                    @forelse($pemeriksaans as $periksa)
                        <tr>
                            <td>{{ $periksa->daftarPoli->pasien->nama }}</td>
                            <td>{{ $periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}</td>
                            <td>{{ $periksa->tgl_periksa->format('d-m-Y H:i') }}</td>
                            <td>{{ $periksa->catatan }}</td>
                            <td>Rp {{ number_format($periksa->biaya_periksa, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-10 text-slate-400">Belum ada pasien yang diperiksa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $pemeriksaans->links() }}</div>
    </div>
</x-layouts.app>
