<?php

namespace JoliMardi\NovaVideoField\Services;


interface VideoServiceInterface {

    // Set Api Key for future api calls
    public function setApiKey(string $apiKey): void;

    // Check if an url is valid for this video service (regexp check if possible)
    public static function isValidUrl(string $url): bool;

    // Get video_id from a valid url
    public static function extractVideoId(string $url): ?string;

    // Get raw data from the api and transform it to normalized VideoData object (get Title an Thumbnail)
    public function fetchVideoData(string $video_id): ?VideoData;

}
