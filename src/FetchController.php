<?php

namespace JoliMardi\NovaVideoField;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use JoliMardi\NovaVideoField\Services\VideoData;

class FetchController {



    // Action qui répond à l'appel Ajax
    public function __invoke(Request $request) {
        $url = $request->input('url');
        // $url = "https://www.youtube.com/watch?v=mYh6Soz3lA4";

        try {
            $videoData = $this->getVideo($url);
        } catch (\Exception $e) {
            $videoData = [
                'message' => $e->getMessage()
            ];
        }

        if (!$videoData) {
            $videoData = [
                'message' => "Impossible de récupérer les données de la vidéo. Est-ce que l'url est bien formatée ?<br><i>Ex. : https://www.youtube.com/watch?v=mYh6Soz3lA4 ou https://vimeo.com/161552105</i>"
            ];
        }

        return response()->json($videoData);
    }


    // -------------------    Logique de récupération    ---------------------------

    public function getVideo(string $url): ?VideoData {

        // Classnames des services et API Keys
        $services = [
            [
                'title' => 'Youtube',
                'machine_name' => 'youtube',
                'classname' => 'JoliMardi\NovaVideoField\Services\Youtube\YoutubeService',
                'apikey' => ENV('YOUTUBE_API_KEY')
            ],
            [
                'title' => 'Vimeo',
                'machine_name' => 'vimeo',
                'classname' => 'JoliMardi\NovaVideoField\Services\Vimeo\VimeoService',
                'apikey' => ENV('VIMEO_TOKEN')
            ],
        ];
        // Convert to object
        $services = json_decode(json_encode($services));


        $service = null;
        $currentService = null;

        foreach ($services as $currentService) {
            if (($currentService->classname)::isValidUrl($url)) {
                $service = new ($currentService->classname)();
                $currentService = $currentService;
                break;
            }
        }

        if (!$service) {
            throw new Exception("Impossible de récupérer l'ID de la vidéo (l'url ne correspond à aucun service). Est-ce que l'url est bien formatée ?<br><i>Ex. : https://www.youtube.com/watch?v=mYh6Soz3lA4 ou https://vimeo.com/161552105</i>");
        }


        $video_id = $service::extractVideoId($url);
        if (!$video_id) {
            throw new Exception("Impossible de récupérer l'ID de la vidéo (service détecté : $currentService->title). Est-ce que l'url est bien formatée ?<br><i>Ex. : https://www.youtube.com/watch?v=mYh6Soz3lA4 ou https://vimeo.com/161552105</i>");
        }

        if (empty($currentService->apikey)) {
            throw new Exception("$currentService->title : La clé d'Api est manquante dans la configuration (YOUTUBE_API_KEY ou VIMEO_TOKEN).");
        }
        $service->setApiKey($currentService->apikey);

        $videoData = $service->fetchVideoData($video_id);

        // On ajoute quelques infos :

        // url originale
        $videoData->original_url = $url;

        // Nom du service utilisé
        $videoData->setService($currentService->machine_name);

        // On copie la thumbnail en local et on remplace le nom
        $url = $videoData->thumbnail_url;
        if (!empty($url)) {
            $extension = pathinfo($url, PATHINFO_EXTENSION);

            // Petit fix pour Vimeo qui n'a pas d'extension
            if ($currentService->machine_name == 'vimeo' && empty($extension)) {
                $url = strtok($url, '?'); // On enlève tout ce qui est get
                $url = $url . '.jpg';
                $extension = 'jpg';
            }

            // On copie dans storage/app/public/{service}/{video_id}.$ext et on le met dans VideoData
            $path = "{$currentService->machine_name}/{$videoData->video_id}.{$extension}"; // vimeo/{video_id}.jpg
            Storage::disk('videos')->put($path, file_get_contents($url), 'public');
            $videoData->thumbnail_url = $path;
        } else {
            $videoData->thumbnail_url = '';
        }

        return $videoData;
    }
}
