<?php

namespace Illuminate\Support {
    if (!function_exists('Illuminate\Support\extension_loaded')) {
        function extension_loaded($name) {
            if ($name === 'intl') {
                return true;
            }
            return \extension_loaded($name);
        }
    }
}

namespace {
    if (!class_exists('NumberFormatter')) {
        class NumberFormatter
        {
            const DECIMAL = 1;
            const PERCENT = 2;
            const CURRENCY = 3;
            const SPELLOUT = 4;
            const ORDINAL = 5;
            const MAX_FRACTION_DIGITS = 6;
            const FRACTION_DIGITS = 7;
            const DEFAULT_RULESET = 8;

            private $locale;
            private $style;
            private $attributes = [];
            private $textAttributes = [];

            public function __construct($locale, $style)
            {
                $this->locale = $locale;
                $this->style = $style;
            }

            public function setAttribute($attribute, $value)
            {
                $this->attributes[$attribute] = $value;
                return true;
            }

            public function setTextAttribute($attribute, $value)
            {
                $this->textAttributes[$attribute] = $value;
                return true;
            }

            public function format($number)
            {
                if ($this->style === self::PERCENT) {
                    $number = $number * 100;
                    $formatted = number_format($number, $this->attributes[self::FRACTION_DIGITS] ?? 0, '.', ' ');
                    return $formatted . '%';
                }

                $precision = $this->attributes[self::FRACTION_DIGITS] ?? $this->attributes[self::MAX_FRACTION_DIGITS] ?? 0;
                return number_format($number, $precision, ',', ' ');
            }

            public function formatCurrency($number, $currency)
            {
                $precision = $this->attributes[self::FRACTION_DIGITS] ?? 2;
                $formatted = number_format($number, $precision, ',', ' ');
                return $formatted . ' ' . $currency;
            }
        }
    }
}

namespace App\Providers {

    use Illuminate\Support\ServiceProvider;
    use Filament\GlobalSearch\Contracts\GlobalSearchProvider;
    use Filament\GlobalSearch\Models\SearchResult;
    use Filament\GlobalSearch\Models\SearchResults;
    use Carbon\Carbon;

    class AppServiceProvider extends ServiceProvider
    {
        /**
         * Register any application services.
         */
        public function register(): void
        {
            // 💡 Liaison du moteur de recherche personnalisé pour Filament v3
            $this->app->bind(GlobalSearchProvider::class, function () {
                return new class implements GlobalSearchProvider {
                    public function getResults(string $query): ?SearchResults
                    {
                        $results = SearchResults::make();

                        // Exemple : Recherche ton tableau de bord personnalisé
                        if (str_contains(strtolower('Tableau de bord'), strtolower($query))) {
                            $results->category('Navigation', [
                                new SearchResult(
                                    title: 'Tableau de bord',
                                    url: '/ressources-humaines', // Ton URL locale
                                ),
                            ]);
                        }

                        // Tu pourras ajouter d'autres conditions if ici pour tes futures pages !
                        return $results;
                    }
                };
            });
        }

        /**
         * Bootstrap any application services.
         */
        public function boot(): void
        {
            // Macro de secours pour translatedFormat si l'extension PHP "intl" est absente
            Carbon::macro('safeTranslatedFormat', function ($format) {
                if (extension_loaded('intl')) {
                    return $this->translatedFormat($format);
                }

                $englishDate = $this->format($format);

                $translations = [
                    'Monday' => 'lundi', 'Tuesday' => 'mardi', 'Wednesday' => 'mercredi',
                    'Thursday' => 'jeudi', 'Friday' => 'vendredi', 'Saturday' => 'samedi', 'Sunday' => 'dimanche',
                    'Mon' => 'lun', 'Tue' => 'mar', 'Wed' => 'mer', 'Thu' => 'jeu', 'Fri' => 'ven', 'Sat' => 'sam', 'Sun' => 'dim',
                    'January' => 'janvier', 'February' => 'février', 'March' => 'mars', 'April' => 'avril', 'May' => 'mai', 'June' => 'juin',
                    'July' => 'juillet', 'August' => 'août', 'September' => 'septembre', 'October' => 'octobre', 'November' => 'novembre', 'December' => 'décembre',
                    'Jan' => 'janv', 'Feb' => 'févr', 'Mar' => 'mars', 'Apr' => 'avr', 'May' => 'mai', 'Jun' => 'juin', 'Jul' => 'juil', 'Aug' => 'août', 'Sep' => 'sept', 'Oct' => 'oct', 'Nov' => 'nov', 'Dec' => 'déc'
                ];
                return strtr($englishDate, $translations);
            });

            // Génération automatique quotidienne des pointages au démarrage du serveur ou au premier accès du jour (uniquement à partir de 8h00)
            try {
                $argv = $_SERVER['argv'] ?? [];
                $isServe = isset($argv[1]) && $argv[1] === 'serve';

                if (! $this->app->runningInConsole() || $isServe) {
                    $now = Carbon::now();
                    if ($now->hour >= 8) {
                        $today = Carbon::today()->toDateString();
                        $cacheKey = 'daily_pointages_generated_' . $today;

                        if (!\Illuminate\Support\Facades\Cache::has($cacheKey)) {
                            \Illuminate\Support\Facades\Cache::put($cacheKey, true, now()->endOfDay());
                            $exists = \Illuminate\Support\Facades\DB::table('pointages')
                                ->whereDate('created_at', $today)
                                ->exists();

                            if (!$exists) {
                                try {
                                    \Illuminate\Support\Facades\Artisan::call('pointages:generate-daily');
                                } catch (\Throwable $t) {
                                    // Ignorer si la commande console n'est pas instanciable dans ce contexte HTTP
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Pointages Boot Error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            }
        }
    }
}