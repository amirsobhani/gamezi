<?php

namespace App\Providers;

use App\Services\UploadService;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Infrastructure\Interfaces\UploadServiceInterface;
use Modules\Game\Entities\Game;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UploadServiceInterface::class, UploadService::class);

        Relation::enforceMorphMap([
            1 => Game::class
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
