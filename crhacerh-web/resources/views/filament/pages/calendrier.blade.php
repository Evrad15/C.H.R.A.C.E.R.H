<x-filament-panels::page>
    <x-filament-actions::modals />

    <div class="p-6 bg-white rounded-xl shadow dark:bg-gray-800">
        @if(!$isConnected)
            <div class="text-center py-12">
                <div class="inline-flex p-4 bg-amber-50 rounded-full dark:bg-amber-900/20 text-amber-500 mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">Synchronisation Google Calendar</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-sm mx-auto mb-6">
                    Connectez votre compte Google pour synchroniser le calendrier et gérer les plannings.
                </p>
                <a href="/google/auth" class="inline-flex items-center px-4 py-2 bg-amber-500 text-white rounded-lg hover:bg-amber-600 transition">
                    Se connecter avec Google
                </a>
            </div>
        @else
            <div wire:ignore x-data="{
                events: @js($events),
                holidays: @js($holidays),
                lastClick: 0,
                init() {
                    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
                        initialView: 'dayGridMonth',
                        locale: 'fr',
                        selectable: true,
                        dateClick: (info) => {
                            let now = new Date().getTime();
                            if (this.lastClick && (now - this.lastClick) < 300) {
                                Livewire.dispatch('open-event-modal', { date: info.dateStr });
                            }
                            this.lastClick = now;
                        },
                        events: [...this.events, ...this.holidays]
                    });
                    calendar.render();
                }
            }">
                <div id="calendar" class="min-h-[600px]"></div>
            </div>

            <div class="mt-8 p-4 bg-gray-50 rounded-lg dark:bg-gray-700">
                <h4 class="font-semibold text-gray-900 dark:text-gray-100 mb-4">Légende</h4>
                <div class="flex gap-4 text-sm">
                    <div class="flex items-center"><div class="w-4 h-4 bg-amber-500 rounded mr-2"></div> Google Calendar</div>
                    <div class="flex items-center"><div class="w-4 h-4 bg-red-600 rounded mr-2"></div> Jours fériés</div>
                </div>
            </div>
        @endif
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/fr.global.js"></script>
</x-filament-panels::page>