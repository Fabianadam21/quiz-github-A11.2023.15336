<x-layouts.app title="Daftar Poli">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Daftar Poli</h1>
        <p class="mt-1 text-slate-500">Pilih poli dan jadwal dokter, lalu sampaikan keluhan Anda.</p>
    </div>

    <section class="panel-card max-w-3xl overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="font-bold text-slate-800">Formulir Pendaftaran</h2>
            <p class="mt-1 text-sm text-slate-500">Pilih poli untuk melihat dokter dan jadwal praktik yang tersedia.</p>
        </div>
        <div class="p-6">
            <form method="POST" action="{{ route('pasien.daftar-poli.store') }}">
                @csrf
                <div class="mb-5">
                    <label for="id_poli" class="mb-2 block text-sm font-semibold text-slate-700">Poli</label>
                    <select id="id_poli" name="id_poli" class="select select-bordered w-full @error('id_poli') select-error @enderror" required>
                        <option value="">Pilih poli</option>
                        @foreach($polis as $poli)
                            <option value="{{ $poli->id }}" @selected(old('id_poli') == $poli->id)>{{ $poli->nama_poli }}</option>
                        @endforeach
                    </select>
                    @error('id_poli')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="mb-5">
                    <label for="id_jadwal" class="mb-2 block text-sm font-semibold text-slate-700">Jadwal Dokter</label>
                    <select id="id_jadwal" name="id_jadwal" class="select select-bordered w-full @error('id_jadwal') select-error @enderror" required disabled>
                        <option value="">Pilih poli terlebih dahulu</option>
                        @foreach($jadwals as $jadwal)
                            <option value="{{ $jadwal->id }}" data-poli-id="{{ $jadwal->dokter->id_poli }}" @selected(old('id_jadwal') == $jadwal->id)>
                                {{ $jadwal->dokter->nama }},
                                {{ $jadwal->hari }} {{ substr($jadwal->jam_mulai, 0, 5) }}–{{ substr($jadwal->jam_selesai, 0, 5) }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_jadwal')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="mb-6">
                    <label for="keluhan" class="mb-2 block text-sm font-semibold text-slate-700">Keluhan</label>
                    <textarea id="keluhan" name="keluhan" rows="4" class="textarea textarea-bordered w-full @error('keluhan') textarea-error @enderror" required>{{ old('keluhan') }}</textarea>
                    @error('keluhan')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <button class="btn rounded-xl border-0 bg-[#2d4499] px-6 text-white shadow-sm hover:bg-[#243d8b]">
                    <i class="fas fa-calendar-check"></i> Daftar Poli
                </button>
            </form>
        </div>
    </section>

    @push('scripts')
    <script>
        const poliSelect = document.getElementById('id_poli');
        const jadwalSelect = document.getElementById('id_jadwal');

        function filterJadwalByPoli() {
            const poliId = poliSelect.value;
            let hasAvailableSchedule = false;

            jadwalSelect.value = '';
            jadwalSelect.querySelectorAll('option[data-poli-id]').forEach((option) => {
                option.hidden = option.dataset.poliId !== poliId;
                hasAvailableSchedule = hasAvailableSchedule || !option.hidden;
            });

            jadwalSelect.disabled = !poliId;
            jadwalSelect.options[0].textContent = !poliId
                ? 'Pilih poli terlebih dahulu'
                : hasAvailableSchedule
                    ? 'Pilih jadwal'
                    : 'Belum ada jadwal aktif';
        }

        poliSelect.addEventListener('change', filterJadwalByPoli);
        const oldScheduleId = @json(old('id_jadwal', ''));
        filterJadwalByPoli();
        const oldSchedule = Array.from(jadwalSelect.options)
            .find((option) => option.value === String(oldScheduleId) && !option.hidden);
        if (oldSchedule) jadwalSelect.value = oldSchedule.value;
    </script>
    @endpush
</x-layouts.app>
