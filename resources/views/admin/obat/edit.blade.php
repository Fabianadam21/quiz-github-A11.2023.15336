<x-layouts.app title="Edit Obat">

    <div class="flex items-center gap-3 mb-6">
        <a href="{{ route('obat.index') }}"
            class="flex items-center justify-center w-9 h-9 rounded-lg bg-slate-100 text-slate-500 hover:bg-slate-200 transition">
            <i class="fas fa-arrow-left text-sm"></i>
        </a>
        <h2 class="text-2xl font-bold text-slate-800">Edit Obat</h2>
    </div>

    <div class="card bg-base-100 shadow-md rounded-2xl border border-slate-200">
        <div class="card-body p-8">
            <form action="{{ route('obat.update', $obat) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-6">
                    <label for="nama_obat" class="block text-sm font-semibold text-slate-700 mb-1">
                        Nama Obat <span class="text-red-500">*</span>
                    </label>
                    <input id="nama_obat" type="text" name="nama_obat"
                        value="{{ old('nama_obat', $obat->nama_obat) }}" maxlength="255"
                        placeholder="Masukkan nama obat..."
                        class="w-full px-4 py-2 rounded-lg border-2 border-slate-300 focus:border-primary focus:outline-none @error('nama_obat') border-red-500 @enderror"
                        required>
                    @error('nama_obat')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-6">
                    <label for="kemasan" class="block text-sm font-semibold text-slate-700 mb-1">
                        Kemasan <span class="text-red-500">*</span>
                    </label>
                    <input id="kemasan" type="text" name="kemasan" value="{{ old('kemasan', $obat->kemasan) }}"
                        maxlength="35" placeholder="Contoh: Strip, Botol..."
                        class="w-full px-4 py-2 rounded-lg border-2 border-slate-300 focus:border-primary focus:outline-none @error('kemasan') border-red-500 @enderror"
                        required>
                    @error('kemasan')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-8">
                    <label for="harga" class="block text-sm font-semibold text-slate-700 mb-1">
                        Harga (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input id="harga" type="number" name="harga" value="{{ old('harga', $obat->harga) }}" min="0"
                        step="1" placeholder="Masukkan harga obat..."
                        class="w-full px-4 py-2 rounded-lg border-2 border-slate-300 focus:border-primary focus:outline-none @error('harga') border-red-500 @enderror"
                        required>
                    @error('harga')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button type="submit"
                        class="px-6 py-2.5 rounded-lg bg-primary hover:bg-primary/90 text-white font-semibold text-sm transition">
                        <i class="fas fa-save mr-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('obat.index') }}"
                        class="px-6 py-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-sm transition">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>

</x-layouts.app>
