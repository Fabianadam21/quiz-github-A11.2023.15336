<header class="h-16 flex items-center px-6 gap-4 sticky top-0 z-30 border-b border-slate-200 bg-white/90 backdrop-blur-sm">
    <button onclick="toggleSidebar()" class="btn btn-square btn-ghost lg:hidden">
        <i class="fas fa-bars"></i>
    </button>

    <div class="flex-1">
        <div class="flex items-center gap-2 text-sm text-slate-500">
            <span class="font-medium">Poliklinik</span>
            <i class="fas fa-chevron-right text-[10px]"></i>
            <span class="font-semibold text-slate-700">{{ $title ?? 'Dashboard' }}</span>
        </div>
    </div>

    <a href="{{ route('notifikasi.index') }}" class="btn btn-square btn-ghost relative" aria-label="Notifikasi">
        <i class="fas fa-bell text-slate-600"></i>
        @php($unreadCount = auth()->user()->unreadNotifications()->count())
        @if($unreadCount > 0)
            <span class="badge badge-error badge-xs absolute top-1 right-1">{{ $unreadCount }}</span>
        @endif
    </a>

    <button onclick="toggleFullscreen()" class="btn btn-square btn-ghost">
        <i id="fsIcon" class="fas fa-expand text-slate-600"></i>
    </button>

    <div class="flex items-center gap-3">
        <div class="hidden text-right sm:block">
            <div class="text-sm font-semibold leading-tight text-slate-700">{{ auth()->user()->nama ?? 'Pengguna' }}</div>
            <div class="text-[11px] uppercase tracking-wide text-slate-400">{{ auth()->user()->role ?? 'Admin Sistem' }}</div>
        </div>

        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-blue-700 text-sm font-bold text-white">
            {{ strtoupper(substr(auth()->user()->nama ?? 'U', 0, 1)) }}
        </div>
    </div>
</header>

<script>
    function toggleFullscreen() {
        const icon = document.getElementById('fsIcon');

        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            icon.classList.remove('fa-expand');
            icon.classList.add('fa-compress');
        } else {
            document.exitFullscreen();
            icon.classList.remove('fa-compress');
            icon.classList.add('fa-expand');
        }
    }
</script>
