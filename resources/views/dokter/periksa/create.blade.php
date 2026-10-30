<x-layouts.app title="Pemeriksaan Pasien">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Pemeriksaan Pasien</h1>
    <section class="card bg-base-100 shadow-sm rounded-2xl border max-w-3xl">
        <div class="card-body">
            <div class="mb-4">
                <p><strong>Pasien:</strong> {{ $daftarPoli->pasien->nama }}</p>
                <p><strong>Nomor antrean:</strong> {{ $daftarPoli->no_antrian }}</p>
                <p><strong>Keluhan:</strong> {{ $daftarPoli->keluhan }}</p>
            </div>
            <form method="POST" action="{{ route('dokter.periksa.store', $daftarPoli) }}">
                @csrf
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-semibold">Catatan Dokter</span></label>
                    <textarea name="catatan" rows="5" class="textarea textarea-bordered @error('catatan') textarea-error @enderror" required>{{ old('catatan') }}</textarea>
                    @error('catatan')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-semibold">Biaya Pemeriksaan (Rp)</span></label>
                    <input type="number" name="biaya_periksa" min="0" value="{{ old('biaya_periksa', 0) }}" class="input input-bordered @error('biaya_periksa') input-error @enderror" required>
                    @error('biaya_periksa')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <fieldset class="form-control mb-6">
                    <legend class="font-semibold mb-2">Obat</legend>
                    @forelse($obats as $obat)
                        <label class="label justify-start gap-3">
                            <input type="checkbox" name="obat[]" value="{{ $obat->id }}" class="checkbox checkbox-primary" @checked(in_array($obat->id, old('obat', [])))>
                            <span>{{ $obat->nama_obat }} — Rp {{ number_format($obat->harga, 0, ',', '.') }}</span>
                        </label>
                    @empty
                        <p class="text-slate-500">Belum ada data obat.</p>
                    @endforelse
                    @error('obat.*')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                </fieldset>
                <button class="btn bg-primary text-white">Simpan Pemeriksaan</button>
                <a href="{{ route('dokter.antrian.index') }}" class="btn btn-ghost">Batal</a>
            </form>
        </div>
    </section>
</x-layouts.app>
