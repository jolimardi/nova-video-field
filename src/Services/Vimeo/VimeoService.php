<?php

namespace JoliMardi\NovaVideoField\Services\Vimeo;

use Exception;
use JsonException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use JoliMardi\NovaVideoField\Services\VideoData;
use JoliMardi\NovaVideoField\Services\VideoServiceInterface;

class VimeoService implements VideoServiceInterface {
    protected string $apikey;

    public function setApiKey(string $apikey): void {
        if (empty($apikey)) {
            throw new Exception("La clé d'Api Viméo est manquante - ENV('VIMEO_TOKEN')");
        }
        $this->apikey = $apikey;
    }

    // Check if an url is valid for this video service
    public static function isValidUrl(string $url): bool {
        return self::extractVideoId($url) !== null ? true : false;
    }

    // Get video_id from a valid url
    public static function extractVideoId(string $url): ?string {
        $regexp = '/https?:\/\/(www\.)?vimeo.com\/(\d+)(\/)?/';
        preg_match($regexp, $url, $matches);

        return $matches[2] ?? null;
    }

    // Get raw data from the api and transform it to normalized VideoData object (get Title an Thumbnail)
    public function fetchVideoData(string $video_id): ?VideoData {

        $apiUrl = "https://api.vimeo.com/videos/{$video_id}";

        $client = new \GuzzleHttp\Client();

        try {
            $response = $client->get($apiUrl, [
                'headers' => [
                    'Authorization' => "Bearer {$this->apikey}",
                    'Content-Type'  => 'application/json'
                ],
                'verify' => false // @TODO ajouter la vérification SSL
            ]);
            $parsedJson = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('HTTP error: ' . $response->getReasonPhrase());
            }




            $response = json_decode($response->getBody(), true);
            $video_data = new VideoData();
            $video_data->setVideoId($video_id);
            $video_data->setTitle($parsedJson['name']);

            $last_thumbnail_index = count($parsedJson['pictures']['sizes']) - 1;
            $video_data->setThumbnailUrl($parsedJson['pictures']['sizes'][$last_thumbnail_index]['link'] ?? null);

            return $video_data;
        } catch (RequestException $e) {
            throw new \Exception('Erreur de requête HTTP. Détails : ' . $e->getMessage() . ' API CALL');
        } catch (JsonException $e) {
            throw new \Exception('Erreur de parsing JSON. Détails : ' . $e->getMessage() . ' JSON parsing');
        }
    }
}
