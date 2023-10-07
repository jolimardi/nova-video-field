<?php

namespace JoliMardi\NovaVideoField\Services;

use Illuminate\Http\Request;


class FetchController {
    public function __invoke(Request $request) {
        $url = $request->input('url');
        // $url = "https://www.youtube.com/watch?v=mYh6Soz3lA4";
        $videoService = app(\JoliMardi\NovaVideoField\Services\VideoService::class);
        $videoData = $videoService->fetchVideoData($url);

        return response()->json($videoData);
    }
}
