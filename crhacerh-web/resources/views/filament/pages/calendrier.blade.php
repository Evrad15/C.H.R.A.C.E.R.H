<x-filament-panels::page>
    <div class="p-6 bg-white rounded-xl shadow dark:bg-gray-800">
        @if(!$isConnected)
        <div class="text-center py-12">
            <div class="inline-flex p-4 bg-amber-50 rounded-full dark:bg-amber-900/20 text-amber-500 mb-4">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Synchronisation Google Calendar</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-6">Connectez le calendrier de CHRACERH à votre compte Google pour afficher et gérer les plannings.</p>
            <a href="/google/auth" class="inline-flex items-center px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-medium rounded-lg transition-colors">
                Se connecter avec Google
            </a>
        </div>
        @else
        <div
            id="calendar-container"
            wire:ignore
            x-data="{
                    events: @js($events),
                    init() {
                        var calendarEl = document.getElementById('calendar');
                        var calendar = new FullCalendar.Calendar(calendarEl, {
                            initialView: 'dayGridMonth',
                            locale: 'fr',
                            headerToolbar: {
                                left: 'prev,next today',
                                center: 'title',
                                right: 'dayGridMonth,timeGridWeek,timeGridDay'
                            },
                            events: this.events,
                            themeSystem: 'standard',
                            eventColor: '#f59e0b'
                        });
                        calendar.render();
                    }
                }">
            <div id="calendar"></div>
        </div>
        @endif
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/fr.global.js"></script>

    <style>
        .dark .fc-theme-standard td,
        .dark .fc-theme-standard th {
            border-color: #374151;
        }

        .dark .fc .fc-toolbar-title,
        .dark .fc .fc-col-header-cell-cushion {
            color: #f3f4f6;
        }
    </style>
</x-filament-panels::page>