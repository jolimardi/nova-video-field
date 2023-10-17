<?php

namespace JoliMardi\NovaVideoField\Services;

class VideoData {
    public $original_url;
    public $video_id;
    public $title;
    public $thumbnail_url;
    public $service; // youtube ou vimeo

    public function __construct(string $original_url = null, string $video_id = null, string $title = null, string $thumbnail_url = null, string $service = null) {
        if ($original_url) {
            $this->original_url = $original_url;
        }
        if ($video_id) {
            $this->video_id = $video_id;
        }
        if ($title) {
            $this->title = $title;
        }
        if ($thumbnail_url) {
            $this->thumbnail_url = $thumbnail_url;
        }
        if ($service) {
            $this->service = $service;
        }
    }


    public function setOriginalUrl(?string $original_url) {
        $this->original_url = $original_url;
    }
    public function setVideoId(?string $video_id) {
        $this->video_id = $video_id;
    }
    public function setTitle(?string $title) {
        $this->title = $title;
    }
    public function setThumbnailUrl(?string $thumbnail_url) {
        $this->thumbnail_url = $thumbnail_url;
    }
    public function setService(?string $service) {
        $this->service = $service;
    }
}
