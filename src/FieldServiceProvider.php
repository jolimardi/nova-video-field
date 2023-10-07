<?php

namespace JoliMardi\NovaVideoField;

use Laravel\Nova\Nova;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\ServiceProvider;
use JoliMardi\NovaVideoField\Services\VideoService;
use JoliMardi\NovaVideoField\Services\FetchController;
use Illuminate\Support\Facades\Route;

class FieldServiceProvider extends ServiceProvider {
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot() {
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
    public function register() {
        $this->app->singleton(VideoService::class, function ($app) {
            try {
                $client = new \GuzzleHttp\Client();

                $youtubeApiKey = ENV('YOUTUBE_API_KEY');
                $vimeoToken = ENV('VIMEO_TOKEN');

                if (empty($youtubeApiKey)) {
                    throw new \RuntimeException('La clé API YouTube est manquante dans la configuration.');
                }

                if (empty($vimeoToken)) {
                    throw new \RuntimeException('Le token Vimeo est manquant dans la configuration.');
                }

                return new VideoService($client, $youtubeApiKey, $vimeoToken);
            } catch (\Exception $e) {
                throw $e;
            }
        });
    }
}
