<x-layouts.app title="Tambah Dokter">
    <div class="mb-6 flex items-center gap-3">
        <a href="{{ route('dokter.index') }}" class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-slate-500 shadow-sm hover:bg-slate-100">
            <i class="fas fa-arrow-left"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Tambah Dokter</h1>
            <p class="mt-1 text-slate-500">Masukkan informasi akun dan poli dokter.</p>
        </div>
    </div>

    <section class="panel-card max-w-3xl overflow-hidden">
        <div class="border-b border-slate-200 px-6 py-4">
            <h2 class="font-bold text-slate-800">Informasi Dokter</h2>
        </div>
        <form action="{{ route('dokter.store') }}" method="POST" class="space-y-5 p-6">
            @csrf

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="nama" class="mb-2 block text-sm font-semibold text-slate-700">Nama Dokter</label>
                    <input id="nama" type="text" name="nama" value="{{ old('nama') }}" class="input input-bordered w-full @error('nama') input-error @enderror" required>
                    @error('nama')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="id_poli" class="mb-2 block text-sm font-semibold text-slate-700">Poli</label>
                    <select id="id_poli" name="id_poli" class="select select-bordered w-full @error('id_poli') select-error @enderror" required>
                        <option value="">Pilih poli</option>
                        @foreach($polis as $poli)
                            <option value="{{ $poli->id }}" @selected(old('id_poli') == $poli->id)>{{ $poli->nama_poli }}</option>
                        @endforeach
                    </select>
                    @error('id_poli')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="no_ktp" class="mb-2 block text-sm font-semibold text-slate-700">Nomor KTP</label>
                    <input id="no_ktp" type="text" name="no_ktp" value="{{ old('no_ktp') }}" maxlength="16" class="input input-bordered w-full @error('no_ktp') input-error @enderror" required>
                    @error('no_ktp')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="no_hp" class="mb-2 block text-sm font-semibold text-slate-700">Nomor HP</label>
                    <input id="no_hp" type="text" name="no_hp" value="{{ old('no_hp') }}" maxlength="15" class="input input-bordered w-full @error('no_hp') input-error @enderror" required>
                    @error('no_hp')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="sm:col-span-2">
                    <label for="alamat" class="mb-2 block text-sm font-semibold text-slate-700">Alamat</label>
                    <textarea id="alamat" name="alamat" rows="3" class="textarea textarea-bordered w-full @error('alamat') textarea-error @enderror" required>{{ old('alamat') }}</textarea>
                    @error('alamat')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="email" class="mb-2 block text-sm font-semibold text-slate-700">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email') }}" class="input input-bordered w-full @error('email') input-error @enderror" required>
                    @error('email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>

                <div>
                    <label for="password" class="mb-2 block text-sm font-semibold text-slate-700">Password</label>
                    <input id="password" type="password" name="password" minlength="6" class="input input-bordered w-full @error('password') input-error @enderror" required>
                    @error('password')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="flex gap-3 border-t border-slate-200 pt-5">
                <button type="submit" class="btn border-0 bg-[#2d4499] text-white hover:bg-[#243d8b]">
                    <i class="fas fa-save"></i> Simpan Dokter
                </button>
                <a href="{{ route('dokter.index') }}" class="btn bg-slate-100 text-slate-600 hover:bg-slate-200">Batal</a>
            </div>
        </form>
    </section>
</x-layouts.app>
