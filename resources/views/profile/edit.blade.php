<x-layouts.app title="Ganti Password">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Ganti Password</h1>
    <section class="card bg-base-100 shadow-sm rounded-2xl border max-w-2xl">
        <div class="card-body">
            <form method="POST" action="{{ route('profile.password.update') }}">
                @csrf
                @method('PUT')
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-semibold">Password lama</span></label>
                    <input type="password" name="current_password" autocomplete="current-password" class="input input-bordered @error('current_password') input-error @enderror" required>
                    @error('current_password')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-control mb-4">
                    <label class="label"><span class="label-text font-semibold">Password baru</span></label>
                    <input type="password" name="password" autocomplete="new-password" class="input input-bordered @error('password') input-error @enderror" required>
                    @error('password')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="form-control mb-6">
                    <label class="label"><span class="label-text font-semibold">Konfirmasi password baru</span></label>
                    <input type="password" name="password_confirmation" autocomplete="new-password" class="input input-bordered @error('password_confirmation') input-error @enderror" required>
                    @error('password_confirmation')<p class="text-sm text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <button class="btn bg-primary text-white">Simpan Password</button>
            </form>
        </div>
    </section>
</x-layouts.app>
