<template>
  <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText" :full-width-content="fullWidthContent">
    <template #field>
      <input :id="field.attribute" type="text" class="w-full form-control form-input form-input-bordered"
        :class="errorClasses" :placeholder="field.name" v-model="value" @input="fetchVideoData" />

      <div v-if="thumbnail">
        <img :src="thumbnail" alt="Video Thumbnail" class="mt-2">
      </div>
    </template>
  </DefaultField>
</template>

<script>
import { FormField, HandlesValidationErrors } from 'laravel-nova'
import axios from 'axios'

export default {
  mixins: [FormField, HandlesValidationErrors],

  props: ['resourceName', 'resourceId', 'field'],

  data() {
    return {
      thumbnail: null
    };
  },

  methods: {
    /*
     * Set the initial, internal value for the field.
     */
    setInitialValue() {
      this.value = this.field.value || ''
    },

    /**
     * Fill the given FormData object with the field's internal value.
     */
    fill(formData) {
      formData.append(this.fieldAttribute, this.value || '')
    },

    async fetchVideoData() {
      try {
        let response = await axios.get('/fetch-video-data', {
          params: {
            url: this.value
          }
        });

        if (response.data && response.data.thumbnail) {
          this.thumbnail = response.data.thumbnail;
        }
      } catch (error) {
        console.error("Erreur lors de la récupération des données de la vidéo:", error);
      }
    },
  },
}
</script>
