<x-layouts.app title="Dashboard Pasien">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Dashboard Pasien</h1>
            <p class="mt-1 text-slate-500">Jadwal poliklinik dan status antrean terbaru.</p>
        </div>
        <a href="{{ route('pasien.daftar-poli.create') }}" class="btn rounded-xl border-0 bg-[#2d4499] text-white shadow-sm hover:bg-[#243d8b]">
            <i class="fas fa-plus"></i> Daftar Poli
        </a>
    </div>

    @if($antrianAktif)
        @php($jadwalAktif = $antrianAktif->jadwalPeriksa)
        @php($nomorDipanggil = $jadwals->firstWhere('id', $jadwalAktif->id)?->nomor_dipanggil)
        <section id="active-queue-banner" data-active-schedule-id="{{ $jadwalAktif->id }}" data-queue-number="{{ $antrianAktif->no_antrian }}" class="queue-card relative mb-6 overflow-hidden p-5 md:p-6">
            <div class="relative z-10 flex flex-col gap-5 md:flex-row md:items-center md:justify-between">
                <div class="space-y-2">
                    <p class="text-xs font-bold uppercase tracking-[0.14em] text-white/85">Antrian aktif Anda</p>
                    <div>
                        <p class="label">Poliklinik</p>
                        <h2 class="text-xl font-extrabold">{{ $jadwalAktif->dokter->poli->nama_poli ?? 'Poli' }}</h2>
                    </div>
                    <div>
                        <p class="label">Dokter</p>
                        <p class="value">{{ $jadwalAktif->dokter->nama }}</p>
                    </div>
                    <div>
                        <p class="label">Jadwal Periksa</p>
                        <p class="value">{{ $jadwalAktif->hari }} ({{ substr($jadwalAktif->jam_mulai, 0, 5) }} - {{ substr($jadwalAktif->jam_selesai, 0, 5) }})</p>
                    </div>
                </div>
                <div class="relative z-10 flex items-stretch gap-3 self-end md:self-auto">
                    <div class="number-box">
                        <span class="small">Nomor Anda</span>
                        <span class="big">{{ $antrianAktif->no_antrian }}</span>
                    </div>
                    <div class="number-box served">
                        <span class="small">Sedang Dilayani</span>
                        <span class="big" data-current-queue="{{ $jadwalAktif->id }}">{{ $nomorDipanggil ?? '–' }}</span>
                        <span class="live-label"><i class="fas fa-circle"></i> Live Update</span>
                    </div>
                </div>
            </div>
        </section>
    @else
        <section class="panel-card mb-6 px-6 py-4 text-sm font-medium text-slate-600">
            <i class="fas fa-info-circle mr-2"></i>Anda belum memiliki antrean aktif.
        </section>
    @endif

    <section class="panel-card overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h2 class="text-lg font-bold text-slate-800">Jadwal Poliklinik dan Dokter</h2>
        </div>

        <div class="overflow-x-auto">
            <table class="table w-full text-left">
                <thead class="bg-slate-50 text-slate-600 uppercase text-[11px] tracking-wide">
                    <tr>
                        <th class="px-6 py-3">Nomor</th>
                        <th class="px-6 py-3">Poli</th>
                        <th class="px-6 py-3">Dokter</th>
                        <th class="px-6 py-3">Hari</th>
                        <th class="px-6 py-3">Jam Periksa</th>
                        <th class="px-6 py-3">Sedang Dipanggil</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwals as $index => $jadwal)
                        <tr data-schedule-id="{{ $jadwal->id }}" class="border-t border-slate-200">
                            <td class="px-6 py-4 text-slate-700">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $jadwal->dokter->poli->nama_poli ?? '-' }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $jadwal->dokter->nama }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ $jadwal->hari }}</td>
                            <td class="px-6 py-4 text-slate-700">{{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                            <td class="px-6 py-4 text-slate-700" data-queue-number>{{ $jadwal->nomor_dipanggil ?? 'Belum ada' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-slate-400">Belum ada jadwal poliklinik aktif.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    @push('scripts')
    <script>
        document.querySelectorAll('[data-schedule-id]').forEach((row) => {
            const scheduleId = row.dataset.scheduleId;
            window.Echo?.channel(`jadwal.${scheduleId}`).listen('.queue.updated', (event) => {
                const number = event.nomor_antrian ?? 'Belum ada';
                const cell = row.querySelector('[data-queue-number]');
                if (cell) cell.textContent = number;
                const bannerNumber = document.querySelector(`[data-current-queue="${scheduleId}"]`);
                if (bannerNumber) bannerNumber.textContent = number;
                const activeBanner = document.getElementById('active-queue-banner');
                if (activeBanner
                    && Number(activeBanner.dataset.activeScheduleId) === Number(event.jadwal_id)
                    && Number(activeBanner.dataset.queueNumber) === Number(event.completed_queue_number)) {
                    activeBanner.remove();
                }
            });
        });
    </script>
    @endpush
</x-layouts.app>
