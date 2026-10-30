<aside class="w-full bg-transparent text-slate-300 flex flex-col min-h-screen px-0">
    <div class="px-4 py-5 border-b border-white/10">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 rounded-full bg-white/10 border border-white/20 flex items-center justify-center text-2xl font-black text-white shadow-sm">
                K
            </div>
            <div>
                <div class="font-bold text-white text-[1.05rem] leading-tight">Poliklinik</div>
                @if(request()->is('admin*'))
                    <span class="inline-block mt-1 text-[10px] font-bold uppercase tracking-[0.14em] bg-indigo-400/20 text-indigo-200 border border-indigo-300/30 px-2 py-0.5 rounded-md">Admin</span>
                @elseif(request()->is('dokter*'))
                    <span class="inline-block mt-1 text-[10px] font-bold uppercase tracking-[0.14em] bg-violet-400/20 text-violet-200 border border-violet-300/30 px-2 py-0.5 rounded-md">Dokter</span>
                @elseif(request()->is('pasien*'))
                    <span class="inline-block mt-1 text-[10px] font-bold uppercase tracking-[0.14em] bg-amber-400/20 text-amber-200 border border-amber-300/30 px-2 py-0.5 rounded-md">Pasien</span>
                @endif
            </div>
        </div>
    </div>

    <div class="flex-1 overflow-y-auto px-3 py-4">
        @php
            $userRole = auth()->user()?->role;
            $isAdmin = $userRole === 'admin' || request()->is('admin*');
            $isPasien = $userRole === 'pasien' || request()->is('pasien*');
            $isDokter = $userRole === 'dokter' || request()->is('dokter*');

            $baseLink = 'flex items-center gap-3 px-4 py-3 rounded-lg text-sm transition-all duration-200';
            $inactive = 'text-slate-200 hover:bg-white/10 hover:text-white';
            $active = 'bg-white/10 text-white font-semibold shadow-inner border border-white/10';
        @endphp

        @if($isAdmin)
            <p class="menu-label">Menu Admin</p>
            <div class="space-y-1">
                <a href="{{ route('admin.dashboard') }}" class="{{ $baseLink }} {{ request()->routeIs('admin.dashboard') ? $active : $inactive }}"><i class="fas fa-gauge-high w-4 text-center"></i> Dashboard Admin</a>
                <a href="{{ route('polis.index') }}" class="{{ $baseLink }} {{ request()->routeIs('polis.*') ? $active : $inactive }}"><i class="fas fa-hospital w-4 text-center"></i> Manajemen Poli</a>
                <a href="{{ route('dokter.index') }}" class="{{ $baseLink }} {{ request()->routeIs('dokter.*') ? $active : $inactive }}"><i class="fas fa-user-doctor w-4 text-center"></i> Manajemen Dokter</a>
                <a href="{{ route('pasien.index') }}" class="{{ $baseLink }} {{ request()->routeIs('pasien.*') ? $active : $inactive }}"><i class="fas fa-bed-pulse w-4 text-center"></i> Manajemen Pasien</a>
                <a href="{{ route('obat.index') }}" class="{{ $baseLink }} {{ request()->routeIs('obat.*') ? $active : $inactive }}"><i class="fas fa-pills w-4 text-center"></i> Manajemen Obat</a>
                <a href="{{ route('admin.pembayaran.index') }}" class="{{ $baseLink }} {{ request()->routeIs('admin.pembayaran.*') ? $active : $inactive }}"><i class="fas fa-money-check-dollar w-4 text-center"></i> Verifikasi Pembayaran</a>
                <a href="{{ route('profile.edit') }}" class="{{ $baseLink }} {{ request()->routeIs('profile.*') ? $active : $inactive }}"><i class="fas fa-user-gear w-4 text-center"></i> Ganti Password</a>
            </div>
        @endif

        @if($isPasien)
            <p class="menu-label">Menu Pasien</p>
            <div class="space-y-1">
                <a href="{{ route('pasien.dashboard') }}" class="{{ $baseLink }} {{ request()->routeIs('pasien.dashboard') ? $active : $inactive }}"><i class="fas fa-house-medical w-4 text-center"></i> Dashboard Pasien</a>
                <a href="{{ route('pasien.daftar-poli.create') }}" class="{{ $baseLink }} {{ request()->routeIs('pasien.daftar-poli.*') ? $active : $inactive }}"><i class="fas fa-clipboard-list w-4 text-center"></i> Daftar Poli</a>
                <a href="{{ route('pasien.riwayat.index') }}" class="{{ $baseLink }} {{ request()->routeIs('pasien.riwayat.*') ? $active : $inactive }}"><i class="fas fa-clock-rotate-left w-4 text-center"></i> Riwayat Pendaftaran</a>
                <a href="{{ route('pasien.pembayaran.index') }}" class="{{ $baseLink }} {{ request()->routeIs('pasien.pembayaran.*') ? $active : $inactive }}"><i class="fas fa-receipt w-4 text-center"></i> Pembayaran</a>
                <a href="{{ route('profile.edit') }}" class="{{ $baseLink }} {{ request()->routeIs('profile.*') ? $active : $inactive }}"><i class="fas fa-user-gear w-4 text-center"></i> Ganti Password</a>
            </div>
        @endif

        @if($isDokter)
            <p class="menu-label">Menu Dokter</p>
            <div class="space-y-1">
                <a href="{{ route('dokter.dashboard') }}" class="{{ $baseLink }} {{ request()->routeIs('dokter.dashboard') ? $active : $inactive }}"><i class="fas fa-stethoscope w-4 text-center"></i> Dashboard Dokter</a>
                <a href="{{ route('jadwal-periksa.index') }}" class="{{ $baseLink }} {{ request()->routeIs('jadwal-periksa.*') ? $active : $inactive }}"><i class="fas fa-calendar-days w-4 text-center"></i> Jadwal Periksa</a>
                <a href="{{ route('dokter.antrian.index') }}" class="{{ $baseLink }} {{ request()->routeIs('dokter.antrian.*') || request()->routeIs('dokter.periksa.*') ? $active : $inactive }}"><i class="fas fa-users w-4 text-center"></i> Antrian Pasien</a>
                <a href="{{ route('dokter.riwayat.index') }}" class="{{ $baseLink }} {{ request()->routeIs('dokter.riwayat.*') ? $active : $inactive }}"><i class="fas fa-file-medical w-4 text-center"></i> Riwayat Pasien</a>
                <a href="{{ route('profile.edit') }}" class="{{ $baseLink }} {{ request()->routeIs('profile.*') ? $active : $inactive }}"><i class="fas fa-user-gear w-4 text-center"></i> Ganti Password</a>
            </div>
        @endif
    </div>

    <div class="p-4 border-t border-white/10">
        <form method="POST" action="/logout">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold transition-all">
                <i class="fas fa-right-from-bracket w-4"></i>
                Keluar
            </button>
        </form>
    </div>
</aside>
