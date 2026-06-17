<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                Welcome back, {{ $this->welcomeName() }}
            </h2>
            @foreach ($this->welcomeRoles() as $role)
                <x-filament::badge color="primary">{{ \Illuminate\Support\Str::headline($role) }}</x-filament::badge>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
