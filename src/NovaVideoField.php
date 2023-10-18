<?php

namespace JoliMardi\NovaVideoField;

use Laravel\Nova\Fields\Field;
use Illuminate\Support\Str;
use Laravel\Nova\Http\Requests\NovaRequest;

class NovaVideoField extends Field {

    public $multiple = false;

    public function multiple(){
        $this->multiple = true;
        return $this->withMeta(['multiple' => $this->multiple]);;
    }

    /**
     * The field's component.
     *
     * @var string
     */
    public $component = 'nova-video-field';

    //protected $modelAttribute = 'video';

    /*public function setModelAttribute($attributeName) {
        $this->modelAttribute = $attributeName;
        return $this;
    }*/

    /**
     * Resolve the field's value.
     *
     * @param  mixed  $resource
     * @param  string|null  $attribute
     * @return $this
     */
    /*public function resolve($resource, $attribute = null) {
        parent::resolve($resource, $attribute);

        $data = json_decode($this->value, true);
        if ($data && isset($data['url']) && !empty($data['url'])) {
            $this->value = $data['url'];
        }

        return $this;
    }*/


    /**
     * Fill the model's attribute with data.
     *
     * @param  \Illuminate\Database\Eloquent\Model|\Laravel\Nova\Support\Fluent  $model
     * @param  mixed  $value
     * @param  string  $attribute
     * @return void
     */
    /*public function fillModelWithData(mixed $model, mixed $value, string $attribute) {

        $videoUrl = $value;

        $processed_value = app(\JoliMardi\NovaVideoField\Services\VideoService::class)->fetchAndSaveVideoData($videoUrl);

        if ($processed_value) {

            $attributes = [Str::replace('.', '->', $attribute) => $processed_value];

            $model->forceFill($attributes);
        }
    }*/
}
