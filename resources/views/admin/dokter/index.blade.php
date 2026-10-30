<x-layouts.app title="Data Dokter">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-slate-800">Data Dokter</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.export.dokter') }}" class="inline-flex h-10 w-36 items-center justify-center gap-2 border border-primary px-3 text-sm font-semibold text-primary rounded-xl transition hover:bg-primary hover:text-white">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
            <a href="{{ route('dokter.create') }}" class="btn bg-primary text-white rounded-lg">
                <i class="fas fa-plus"></i> Tambah Dokter
            </a>
        </div>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2xl border">
        <div class="card-body p-0 overflow-x-auto">
            <table class="table w-full">
                <thead class="bg-slate-50 text-slate-500 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4">Nama Dokter</th>
                        <th class="px-6 py-4">Poli</th>
                        <th class="px-6 py-4">Email</th>
                        <th class="px-6 py-4">No. HP</th>
                        <th class="px-6 py-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dokters as $dokter)
                        <tr class="border-t border-slate-100">
                            <td class="px-6 py-4 font-semibold">{{ $dokter->nama }}</td>
                            <td class="px-6 py-4">{{ $dokter->poli?->nama_poli ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $dokter->email }}</td>
                            <td class="px-6 py-4">{{ $dokter->no_hp ?? '-' }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.dokter.jadwal', $dokter) }}" class="btn btn-sm bg-[#2d4499] text-white hover:bg-[#1e2d6b]">
                                        Jadwal
                                    </a>
                                    <a href="{{ route('dokter.edit', $dokter) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('dokter.destroy', $dokter) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-error text-white" onclick="return confirm('Hapus dokter ini?')">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-12 text-slate-400">Belum ada data dokter.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>