<x-layouts.app title="Notifikasi">
    <h1 class="text-2xl font-bold text-slate-800 mb-6">Notifikasi</h1>
    <div class="space-y-3">
        @forelse($notifications as $notification)
            <article class="rounded-xl border p-4 {{ $notification->read_at ? 'bg-base-100 border-slate-200' : 'bg-indigo-50 border-indigo-200' }}">
                <div class="flex justify-between gap-4">
                    <div>
                        <p class="font-semibold">{{ $notification->data['message'] ?? 'Notifikasi baru' }}</p>
                        <time class="text-sm text-slate-500">{{ $notification->created_at->diffForHumans() }}</time>
                    </div>
                    @if(!$notification->read_at)
                        <form method="POST" action="{{ route('notifikasi.read', $notification->id) }}">
                            @csrf
                            @method('PATCH')
                            <button class="btn btn-sm btn-outline">Tandai dibaca</button>
                        </form>
                    @endif
                </div>
                @if(!empty($notification->data['url']))
                    <a href="{{ $notification->data['url'] }}" class="link link-primary text-sm mt-2 inline-block">Buka</a>
                @endif
            </article>
        @empty
            <p class="rounded-xl bg-base-100 p-6 text-slate-500">Tidak ada notifikasi.</p>
        @endforelse
        <div>{{ $notifications->links() }}</div>
    </div>
</x-layouts.app>
