<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Filament\GlobalSearch\Contracts\GlobalSearchProvider;
use Filament\GlobalSearch\Models\SearchResult;
use Filament\GlobalSearch\Models\SearchResults;

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
        //
    }
}