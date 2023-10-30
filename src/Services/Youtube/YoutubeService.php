<?php

namespace JoliMardi\NovaVideoField\Services\Youtube;

use Exception;
use JsonException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JoliMardi\NovaVideoField\Services\VideoData;
use JoliMardi\NovaVideoField\Services\VideoServiceInterface;

class YoutubeService implements VideoServiceInterface {

    protected string $apikey;

    public function setApiKey(string $apikey): void {
        if (empty($apikey)) {
            throw new Exception("La clé d'Api Youtube est manquante - ENV('YOUTUBE_API_KEY')");
        }
        $this->apikey = $apikey;
    }

    // Check if an url is valid for this video service (regexp check if possible)
    public static function isValidUrl(string $url): bool {
        return preg_match('/^https:\/\/(?:.*?)\.?(youtube)\.com\/(watch\?[^#]*v=([-\w]+)|(\d+))/', $url);
    }

    // Get video_id from a valid url
    public static function extractVideoId(string $url): ?string {
        $regexp = '/(https?:\/\/)?(www.)?(youtube\.com|youtu\.be|youtube-nocookie\.com)\/(?:embed\/|v\/|watch\?v=|watch\?list=(.*)&v=|watch\?(.*[^&]&)v=)?((\w|-){11})(&list=(\w+)&?)?/ims';
        preg_match($regexp, $url, $matches);

        return $matches[6] ?? null;
    }

    // Get raw data from the api and transform it to normalized VideoData object (get Title an Thumbnail)
    public function fetchVideoData(string $video_id): ?VideoData {

        $apiUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id={$video_id}&key={$this->apikey}";

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->get($apiUrl, ['verify' => false]); // @TODO ajouter la vérification SSL

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('HTTP error: ' . $response->getReasonPhrase());
            }

            $response = json_decode($response->getBody(), true);
            $video_data = new VideoData();
            $video_data->setVideoId($video_id);
            $video_data->setTitle($response['items'][0]['snippet']['title']);

            // Thumbnail, de la plus grande à la plus petite
            if (isset($response['items'][0]['snippet']['thumbnails']['maxres']['url'])) {
                $video_data->setThumbnailUrl($response['items'][0]['snippet']['thumbnails']['maxres']['url'] ?? null);
            } elseif (isset($response['items'][0]['snippet']['thumbnails']['standard']['url'])) {
                $video_data->setThumbnailUrl($response['items'][0]['snippet']['thumbnails']['standard']['url'] ?? null);
            } elseif (isset($response['items'][0]['snippet']['thumbnails']['high']['url'])) {
                $video_data->setThumbnailUrl($response['items'][0]['snippet']['thumbnails']['high']['url'] ?? null);
            } elseif (isset($response['items'][0]['snippet']['thumbnails']['medium']['url'])) {
                $video_data->setThumbnailUrl($response['items'][0]['snippet']['thumbnails']['medium']['url'] ?? null);
            } elseif (isset($response['items'][0]['snippet']['thumbnails']['default']['url'])) {
                $video_data->setThumbnailUrl($response['items'][0]['snippet']['thumbnails']['default']['url'] ?? null);
            }

            return $video_data;
        } catch (RequestException $e) {
            throw new \Exception('Erreur de requête HTTP. Détails : ' . $e->getMessage() . ' API CALL');
        } catch (JsonException $e) {
            throw new \Exception('Erreur de parsing JSON. Détails : ' . $e->getMessage() . ' JSON parsing');
        }
    }





    // ============= VIMEO FUNCTIONS =============

    /*private function fetchVimeoData($url) {
        $videoId = $this->extractVimeoVideoId($url);
        $apiUrl = "https://api.vimeo.com/videos/{$videoId}";

        try {
            $response = $this->client->get($apiUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$this->vimeoToken}",
                    'Content-Type'  => 'application/json'
                ],
                'verify' => false // @TODO ajouter la vérification SSL
            ]);
            $parsedJson = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('HTTP error: ' . $response->getReasonPhrase());
            }

            return [
                'title'     => $parsedJson['name'],
                'thumbnail' => $parsedJson['pictures']['sizes'][0]['link'] ?? null
            ];
        } catch (RequestException $e) {
            throw new \Exception('Erreur de requête HTTP. Détails : ' . $e->getMessage() . ' API CALL');
        } catch (JsonException $e) {
            throw new \Exception('Erreur de parsing JSON. Détails : ' . $e->getMessage() . ' JSON parsing');
        }
    }

    private function extractVimeoVideoId($url) {
        $regexp = '/https?:\/\/(www\.)?vimeo.com\/(\d+)(\/)?/';
        preg_match($regexp, $url, $matches);

        return $matches[2] ?? null;
    }*/
}
