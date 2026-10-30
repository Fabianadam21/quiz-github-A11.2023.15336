<x-layouts.app title="Riwayat Pendaftaran">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Riwayat Pendaftaran Poli</h1>
        <p class="mt-1 text-slate-500">Daftar pendaftaran dan status pemeriksaan Anda.</p>
    </div>

    <section class="panel-card overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="font-bold text-slate-800">Riwayat Anda</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-[11px] uppercase tracking-wide text-slate-600">
                    <tr>
                        <th class="px-5 py-3">Poli</th>
                        <th class="px-5 py-3">Dokter</th>
                        <th class="px-5 py-3">Tanggal Daftar</th>
                        <th class="px-5 py-3">Jadwal Periksa</th>
                        <th class="px-5 py-3">No. Antrean</th>
                        <th class="px-5 py-3">Status</th>
                        <th class="px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pendaftaran as $item)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-4 font-medium text-slate-700">{{ $item->jadwalPeriksa->dokter->poli->nama_poli ?? '-' }}</td>
                            <td class="px-5 py-4 text-slate-700">{{ $item->jadwalPeriksa->dokter->nama }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $item->created_at->format('d-m-Y') }}</td>
                            <td class="px-5 py-4 text-slate-600">{{ $item->jadwalPeriksa->hari }} {{ substr($item->jadwalPeriksa->jam_mulai, 0, 5) }}–{{ substr($item->jadwalPeriksa->jam_selesai, 0, 5) }}</td>
                            <td class="px-5 py-4"><span class="inline-flex min-w-9 justify-center rounded-full bg-indigo-50 px-3 py-1 font-bold text-indigo-700">{{ $item->no_antrian }}</span></td>
                            <td class="px-5 py-4">
                                @if($item->periksas->isNotEmpty())
                                    <span class="badge badge-success badge-outline">Sudah diperiksa</span>
                                @else
                                    <span class="badge badge-warning badge-outline">Menunggu pemeriksaan</span>
                                @endif
                            </td>
                            <td class="px-5 py-4">
                                @if($item->periksas->isNotEmpty())
                                    <a href="{{ route('pasien.riwayat.show', $item) }}" class="btn btn-sm rounded-lg border-indigo-200 text-indigo-700 hover:bg-indigo-50">Detail</a>
                                @else
                                    <span class="text-slate-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-10 text-slate-400">Belum ada riwayat pendaftaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-slate-100 p-4">{{ $pendaftaran->links() }}</div>
    </section>
</x-layouts.app>
