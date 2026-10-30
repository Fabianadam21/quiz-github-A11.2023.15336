<x-layouts.app title="Jadwal Dokter">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('dokter.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-500 shadow-sm hover:bg-slate-100">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Jadwal Dokter</h1>
            <p class="mt-1 text-slate-500">{{ $dokter->nama }} · {{ $dokter->poli?->nama_poli ?? 'Belum ada poli' }}</p>
        </div>
    </div>

    <section class="panel-card mb-6 overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="font-bold text-slate-800">Tambah Jadwal Praktik</h2>
        </div>
        <form action="{{ route('admin.dokter.jadwal.store', $dokter) }}" method="POST" class="grid gap-5 p-6 sm:grid-cols-2 lg:grid-cols-4 lg:items-end">
            @csrf
            <div>
                <label for="hari" class="mb-2 block text-sm font-semibold text-slate-700">Hari</label>
                <select id="hari" name="hari" class="select select-bordered w-full @error('hari') select-error @enderror" required>
                    <option value="">Pilih hari</option>
                    @foreach($hari as $day)
                        <option value="{{ $day }}" @selected(old('hari') === $day)>{{ $day }}</option>
                    @endforeach
                </select>
                @error('hari')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="jam_mulai" class="mb-2 block text-sm font-semibold text-slate-700">Jam Mulai</label>
                <input id="jam_mulai" type="time" name="jam_mulai" value="{{ old('jam_mulai') }}" class="input input-bordered w-full @error('jam_mulai') input-error @enderror" required>
                @error('jam_mulai')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="jam_selesai" class="mb-2 block text-sm font-semibold text-slate-700">Jam Selesai</label>
                <input id="jam_selesai" type="time" name="jam_selesai" value="{{ old('jam_selesai') }}" class="input input-bordered w-full @error('jam_selesai') input-error @enderror" required>
                @error('jam_selesai')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="btn h-12 border-0 bg-[#2d4499] text-white hover:bg-[#243d8b]">
                <i class="fas fa-plus"></i> Tambah Jadwal
            </button>
        </form>
    </section>

    <section class="panel-card overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="font-bold text-slate-800">Daftar Jadwal</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="px-6 py-4">Hari</th>
                        <th class="px-6 py-4">Jam Mulai</th>
                        <th class="px-6 py-4">Jam Selesai</th>
                        <th class="px-6 py-4">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalPeriksas as $jadwal)
                        <tr class="border-t border-slate-100">
                            <td class="px-6 py-4 font-semibold text-slate-800">{{ $jadwal->hari }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ substr($jadwal->jam_mulai, 0, 5) }}</td>
                            <td class="px-6 py-4 text-slate-600">{{ substr($jadwal->jam_selesai, 0, 5) }}</td>
                            <td class="px-6 py-4">
                                <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">{{ $jadwal->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-12 text-center text-slate-400">
                                <i class="fas fa-calendar-xmark mb-3 block text-3xl"></i>
                                Belum ada jadwal praktik untuk dokter ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-layouts.app>
