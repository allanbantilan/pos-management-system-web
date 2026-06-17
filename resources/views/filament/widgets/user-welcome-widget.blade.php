<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-col gap-1">
            <h2 class="text-xl font-bold tracking-tight text-gray-950 dark:text-white">
                Welcome back, {{ $this->welcomeName() }}
            </h2>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">
                {{ $this->welcomeRole() }}
            </p>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
