{{-- Type Badge --}}
<div class="mb-3">
    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
        {{ match($record->type_label) {
            'Digital File (PDF, ZIP)' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'Image Gallery' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'Markdown Post' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'Long Article / Tutorial' => 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200',
            'RPG Module / Card Pack / Worldbuilding' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300'
        } }}">
        {{ $record->type_label }}
    </span>
</div>
