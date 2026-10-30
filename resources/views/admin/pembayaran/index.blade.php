<x-layouts.app title="Verifikasi Pembayaran">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Verifikasi Pembayaran</h1>
    <div class="card bg-base-100 shadow-sm rounded-2xl border">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr><th>Pasien</th><th>Poli / Dokter</th><th>Tanggal</th><th>Bukti</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    @forelse($pemeriksaans as $periksa)
                        <tr>
                            <td>{{ $periksa->daftarPoli->pasien->nama }}</td>
                            <td>{{ $periksa->daftarPoli->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }} / {{ $periksa->daftarPoli->jadwalPeriksa->dokter->nama }}</td>
                            <td>{{ $periksa->tgl_periksa->format('d-m-Y H:i') }}</td>
                            <td><a class="link link-primary" href="{{ route('admin.pembayaran.proof', $periksa) }}" target="_blank" rel="noopener">Lihat bukti</a></td>
                            <td>
                                <form method="POST" action="{{ route('admin.pembayaran.verify', $periksa) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm bg-primary text-white">Konfirmasi lunas</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-10 text-slate-400">Tidak ada pembayaran yang menunggu verifikasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $pemeriksaans->links() }}</div>
    </div>
</x-layouts.app>
