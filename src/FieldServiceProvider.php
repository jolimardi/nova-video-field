<?php

namespace JoliMardi\NovaVideoField;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;
use JoliMardi\NovaVideoField\VideoService;
use JoliMardi\NovaVideoField\FetchController;
use Illuminate\Support\Facades\Route;

class FieldServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(): void {

        Nova::serving(function (ServingNova $event) {
            Nova::script('nova-video-field', __DIR__ . '/../dist/js/field.js');
            Nova::style('nova-video-field', __DIR__ . '/../dist/css/field.css');
        });

        // Route pour fetch les datas de la vidéo
        Route::get('jolimardi/nova-video-field/fetch', FetchController::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void {
        /*$this->app->singleton(VideoService::class, function ($app) {
            try {
                return new VideoService();
            } catch (\Exception $e) {
                throw $e;
            }
        });*/
    }
}
