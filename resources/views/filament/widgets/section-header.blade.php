<x-filament-widgets::widget>
    <div class="p-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-gray-800 dark:to-gray-700 rounded-lg border-l-4 border-amber-500">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-1">
            {{ $this->getHeading() }}
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ $this->getDescription() }}
        </p>
    </div>
</x-filament-widgets::widget>
