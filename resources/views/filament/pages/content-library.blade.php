<x-filament-panels::page>
    <style>
        /* Force grid layout for content library */
        .fi-ta-content table {
            display: block !important;
            width: 100% !important;
        }

        .fi-ta-content thead {
            display: none !important;
        }

        .fi-ta-content tbody {
            display: grid !important;
            grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
            gap: 1.5rem !important;
            width: 100% !important;
            white-space: normal !important;
        }

        @media (min-width: 768px) {
            .fi-ta-content tbody {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
            }
        }

        @media (min-width: 1024px) {
            .fi-ta-content tbody {
                grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
            }
        }

        @media (min-width: 1280px) {
            .fi-ta-content tbody {
                grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            }
        }

        .fi-ta-content tbody tr {
            display: block !important;
            position: relative !important;
            height: 100% !important;
        }

        .fi-ta-content tbody td {
            display: block !important;
            padding: 0 !important;
            border: none !important;
            height: 100% !important;
        }

        /* Hide actions column (we show icons on cards instead) */
        .fi-ta-content .fi-ta-actions {
            display: none !important;
        }
    </style>

    {{ $this->table }}
</x-filament-panels::page>
