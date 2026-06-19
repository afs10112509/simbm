<div class="fi-wi-widget fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
    <div class="p-4 border-b border-gray-200 dark:border-white/10">
        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $this->getGreeting() }}, {{ auth()->user()->name }}</div>
        <div class="mt-1 text-lg font-semibold text-gray-950 dark:text-white">
            Checklist Harian — {{ $this->getBranchName() }}
        </div>
        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $this->getTodayLabel() }}</div>
        <div class="mt-2 text-sm">
            @if ($this->getRequiredRemaining() > 0)
                <span class="font-medium text-warning-600 dark:text-warning-400">
                    {{ $this->getRequiredRemaining() }} tugas wajib belum selesai
                </span>
            @else
                <span class="font-medium text-success-600 dark:text-success-400">
                    Tugas wajib hari ini sudah selesai ({{ $this->getCompletedCount() }}/{{ $this->getTotalCount() }})
                </span>
            @endif
        </div>
    </div>

    <div class="p-4 space-y-3">
        @foreach ($this->getItems() as $item)
            <div @class([
                'flex items-start justify-between gap-3 rounded-lg border p-3',
                'border-success-500/30 bg-success-500/5' => $item['done'],
                'border-warning-500/30 bg-warning-500/5' => ! $item['done'] && $item['tone'] === 'warning',
                'border-gray-200 bg-gray-50 dark:border-white/10 dark:bg-white/5' => ! $item['done'] && $item['tone'] === 'gray',
            ])>
                <div class="flex items-start gap-3 min-w-0">
                    <div @class([
                        'mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full text-xs font-bold',
                        'bg-success-500 text-white' => $item['done'],
                        'bg-warning-500 text-white' => ! $item['done'] && $item['tone'] === 'warning',
                        'bg-gray-400 text-white' => ! $item['done'] && $item['tone'] === 'gray',
                    ])>
                        {{ $item['done'] ? '✓' : '!' }}
                    </div>
                    <div class="min-w-0">
                        <div class="font-medium text-gray-950 dark:text-white">{{ $item['label'] }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">{{ $item['hint'] }}</div>
                    </div>
                </div>

                @if ($item['url'])
                    <a
                        href="{{ $item['url'] }}"
                        class="shrink-0 text-xs font-semibold text-primary-600 hover:underline dark:text-primary-400"
                    >
                        Buka
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</div>
