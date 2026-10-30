<x-layouts.app title="Dashboard Dokter">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard Dokter</h1>
            <p class="mt-1 text-slate-500">Antrean yang belum diperiksa pada jadwal Anda.</p>
        </div>
        <a href="{{ route('dokter.antrian.index') }}" class="btn bg-primary text-white">Lihat Antrean</a>
    </div>
    <div class="card bg-base-100 shadow-sm rounded-2xl border">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr><th>Poli</th><th>Hari</th><th>Jam Periksa</th><th>Nomor Dipanggil</th></tr>
                </thead>
                <tbody>
                    @forelse($jadwals as $jadwal)
                        <tr data-schedule-id="{{ $jadwal->id }}">
                            <td>{{ $jadwal->dokter->poli->nama_poli ?? '-' }}</td>
                            <td>{{ $jadwal->hari }}</td>
                            <td>{{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                            <td data-queue-number>{{ $jadwal->nomor_dipanggil ?? 'Belum ada' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center py-10 text-slate-400">Belum ada jadwal pemeriksaan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @push('scripts')
    <script>
        document.querySelectorAll('[data-schedule-id]').forEach((row) => {
            window.Echo?.channel(`jadwal.${row.dataset.scheduleId}`).listen('.queue.updated', (event) => {
                const cell = row.querySelector('[data-queue-number]');
                if (cell) cell.textContent = event.nomor_antrian ?? 'Belum ada';
            });
        });
    </script>
    @endpush
</x-layouts.app>
