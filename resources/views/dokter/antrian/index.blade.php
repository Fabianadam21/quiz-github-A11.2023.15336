<x-layouts.app title="Antrian Pasien">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Antrian Pasien</h1>
    @forelse($jadwals as $jadwal)
        <section class="card bg-base-100 shadow-sm rounded-2xl border mb-6" data-schedule-id="{{ $jadwal->id }}">
            <div class="card-body p-0">
                <div class="p-5 border-b border-slate-100">
                    <h2 class="font-bold text-lg">{{ $jadwal->dokter->poli->nama_poli ?? 'Poli' }}</h2>
                    <p class="text-sm text-slate-500">{{ $jadwal->hari }}, {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}</p>
                    <p class="text-sm mt-2">Sedang dipanggil: <strong data-queue-number>{{ $jadwal->nomor_dipanggil ?? 'Belum ada' }}</strong></p>
                </div>
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                            <tr><th>Nomor</th><th>Pasien</th><th>Keluhan</th><th>Aksi</th></tr>
                        </thead>
                        <tbody>
                            @forelse($jadwal->daftarPolis as $item)
                                <tr>
                                    <td>{{ $item->no_antrian }}</td>
                                    <td>{{ $item->pasien->nama }}</td>
                                    <td>{{ $item->keluhan }}</td>
                                    <td><a href="{{ route('dokter.periksa.create', $item) }}" class="btn btn-sm bg-primary text-white">Periksa</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-8 text-slate-400">Tidak ada antrean.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </section>
    @empty
        <div class="rounded-xl bg-base-100 p-6 text-slate-500">Belum ada jadwal periksa.</div>
    @endforelse

    @push('scripts')
    <script>
        document.querySelectorAll('[data-schedule-id]').forEach((card) => {
            window.Echo?.channel(`jadwal.${card.dataset.scheduleId}`).listen('.queue.updated', (event) => {
                const number = card.querySelector('[data-queue-number]');
                if (number) number.textContent = event.nomor_antrian ?? 'Belum ada';
            });
        });
    </script>
    @endpush
</x-layouts.app>
