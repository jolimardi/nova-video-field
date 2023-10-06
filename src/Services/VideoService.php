<?php

namespace JoliMardi\NovaVideoField\Services;

use JsonException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class VideoService {
    protected $client;
    protected $youtubeApiKey;
    protected $vimeoToken;

    public function __construct(Client $client, $youtubeApiKey, $vimeoToken) {
        $this->client = $client;
        $this->youtubeApiKey = $youtubeApiKey;
        $this->vimeoToken = $vimeoToken;
    }

    public function fetchAndSaveVideoData($url, $model, $attributeName = 'video') {
        $dataToStore = [
            'url' => $url,
            'title' => null,
            'thumbnail' => null
        ];

        try {
            $data = null;
            if (preg_match('/^https:\/\/(?:.*?)\.?(youtube)\.com\/(watch\?[^#]*v=(\w+)|(\d+))/', $url)) {
                $data = $this->fetchYoutubeData($url);
            } elseif (preg_match('/^https:\/\/(?:.*?)\.?(vimeo)\.com\/(watch\?[^#]*v=(\w+)|(\d+))/', $url)) {
                $data = $this->fetchVimeoData($url);
            }

            if ($data) {
                $dataToStore['title'] = $data['title'];
                $dataToStore['thumbnail'] = $data['thumbnail'];
            }

            $model->$attributeName = json_encode($dataToStore);
            $model->save();
        } catch (\Exception $e) {
            throw new \Exception('Erreur lors de la récupération des données de la vidéo. Détails : ' . $e->getMessage());
        }
    }

    // ============= YOUTUBE FUNCTIONS =============

    private function fetchYoutubeData($url) {
        $videoId = $this->extractYoutubeVideoId($url);
        $apiUrl = "https://www.googleapis.com/youtube/v3/videos?part=snippet&id={$videoId}&key={$this->youtubeApiKey}";

        try {
            $response = $this->client->get($apiUrl, ['verify' => false]); // @TODO ajouter la vérification SSL
            $parsedJson = json_decode($response->getBody(), true);

            if ($response->getStatusCode() !== 200) {
                throw new \Exception('HTTP error: ' . $response->getReasonPhrase());
            }

            return [
                'title' => $parsedJson['items'][0]['snippet']['title'],
                'thumbnail' => $parsedJson['items'][0]['snippet']['thumbnails']['maxres']['url'] ?? null
            ];
        } catch (RequestException $e) {
            throw new \Exception('Erreur de requête HTTP. Détails : ' . $e->getMessage() . ' API CALL');
        } catch (JsonException $e) {
            throw new \Exception('Erreur de parsing JSON. Détails : ' . $e->getMessage() . ' JSON parsing');
        }
    }

    // Extractions de l'ID de la vidéo pour Youtube
    private function extractYoutubeVideoId($url) {
        $regexp = '/(https?:\/\/)?(www.)?(youtube\.com|youtu\.be|youtube-nocookie\.com)\/(?:embed\/|v\/|watch\?v=|watch\?list=(.*)&v=|watch\?(.*[^&]&)v=)?((\w|-){11})(&list=(\w+)&?)?/ims';
        preg_match($regexp, $url, $matches);

        return $matches[6] ?? null;
    }

    // ============= VIMEO FUNCTIONS =============

    private function fetchVimeoData($url) {
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
    }

    // ============= THUMBNAIL FUNCTIONS =============

    public function fetchVideoData($url) {
        $data = null;
        if (preg_match('/^https:\/\/(?:.*?)\.?(youtube)\.com\/(watch\?[^#]*v=(\w+)|(\d+))/', $url)) {
            $data = $this->fetchYoutubeData($url);
        } elseif (preg_match('/^https:\/\/(?:.*?)\.?(vimeo)\.com\/(watch\?[^#]*v=(\w+)|(\d+))/', $url)) {
            $data = $this->fetchVimeoData($url);
        }
        return $data;
    }
}
