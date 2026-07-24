<?php

namespace App\Providers;

use App\View\Components\Button\BackButton;
use App\View\Components\Button\CreateButton;
use App\View\Components\Button\DeleteButton;
use App\View\Components\Button\EditButton;
use App\View\Components\Button\InfoButton;
use App\View\Components\Button\PrintButton;
use App\View\Components\Button\ResetButton;
use App\View\Components\Button\ShowButton;
use App\View\Components\Table\Action;
use App\View\Components\Table\Table;
use App\View\Components\Table\Td;
use App\View\Components\Table\Tr;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use MatanYadaev\EloquentSpatial\EloquentSpatial;
use MatanYadaev\EloquentSpatial\Enums\Srid;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $locale = config('app.locale');
        Carbon::setLocale($locale);
        setlocale(LC_TIME, $locale, 'de_DE.utf8', 'de');
        URL::forceScheme(env('FORCE_SCHEME', 'https'));
        Schema::defaultStringLength(191);
        Paginator::useBootstrapFive();
        EloquentSpatial::setDefaultSrid(Srid::WGS84);

        Blade::components([
            'table' => Table::class,
            'tr' => Tr::class,
            'td' => Td::class,
            'action' => Action::class,
            'btn-create' => CreateButton::class,
            'btn-edit' => EditButton::class,
            'btn-delete' => DeleteButton::class,
            'btn-back' => BackButton::class,
            'btn-show' => ShowButton::class,
            'btn-info' => InfoButton::class,
            'btn-print' => PrintButton::class,
            'btn-reset' => ResetButton::class,
        ]);
    }
}
