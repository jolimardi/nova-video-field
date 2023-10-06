<?php

namespace JoliMardi\NovaVideoField;

use Laravel\Nova\Fields\Field;
use Laravel\Nova\Http\Requests\NovaRequest;

class NovaVideoField extends Field {
    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-video-field';

    protected $modelAttribute = 'video';

    public function setModelAttribute($attributeName) {
        $this->modelAttribute = $attributeName;
        return $this;
    }

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return $this
     */
    public function resolve($resource, $attribute = null) {
        parent::resolve($resource, $attribute);

        $data = json_decode($this->value, true);
        $this->value = $data['url'] ?? null;

        return $this;
    }

    /**
     * Fill a field's value on an attached resource during an update or creation request.
     *
     * @param  \Laravel\Nova\Http\Requests\NovaRequest  $request
     * @param  $model
     * @return void
     */
    public function fill(\Laravel\Nova\Http\Requests\NovaRequest $request, $model) {
        $videoUrl = $request[$this->attribute];

        app(\JoliMardi\NovaVideoField\Services\VideoService::class)->fetchAndSaveVideoData($videoUrl, $model, $this->modelAttribute);
    }
}
