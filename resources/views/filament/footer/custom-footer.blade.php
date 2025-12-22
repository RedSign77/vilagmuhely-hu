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
                <span>Operated by <a href="https://webtech-solutions.hu" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">Webtech Solutions</a></span>
                <span class="hidden sm:inline">|</span>
                <span>Build: {{ config('version.version') }}</span>
            </div>
            <div class="mt-2 flex flex-col sm:flex-row items-center justify-center gap-2 sm:gap-4">
                <a href="https://webtech-solutions.hu/terms-and-conditions" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">
                    Terms and Conditions
                </a>
                <span class="hidden sm:inline">|</span>
                <a href="https://webtech-solutions.hu/privacy-policy" target="_blank" class="text-primary-600 dark:text-primary-400 hover:underline">
                    Privacy Policy
                </a>
            </div>
        </div>
    </div>
</footer>
