<footer class="fi-footer w-full border-t border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-900">
    <div class="fi-footer-content px-4 py-4 sm:px-6 lg:px-8">
        <div class="text-center text-sm text-gray-600 dark:text-gray-400">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-1">
                <span class="font-semibold text-gray-900 dark:text-gray-100">Világműhely Admin</span>
                <span class="hidden sm:inline">—</span>
                <span class="italic">"Where stories are forged."</span>
            </div>
            <div class="mt-2 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-1">
                <span>&copy; {{ date('Y') }}</span>
                <span class="hidden sm:inline">·</span>
                <span>Operated by Webtech Solutions</span>
                <span class="hidden sm:inline">|</span>
                <span>Build: {{ config('app.version') }}</span>
            </div>
        </div>
    </div>
</footer>
