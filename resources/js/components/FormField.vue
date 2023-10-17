<template>
    <DefaultField :field="field" :errors="errors" :show-help-text="showHelpText" :full-width-content="fullWidthContent">
        <template #field>


            <!-- Affichage des vidéos déjà enregistrées -->

            <div class="videos-list pt-3 pb-4" v-if="videos.length > 0">
                <div v-for="(video, index) in videos">
                    <div class="video">

                        <img :src="video.thumbnail_url" alt="Video Thumbnail">

                        <div class="video-body py-4 px-4">

                            Titre de la vidéo
                            <input type="text" class="w-full mb-4 form-control form-input form-input-bordered" placeholder="Titre de cette vidéo" @input="event => updateVideoTitle(index, event)" :value="video.title" />

                            <div class="actions text-right">
                                <span class="bg-gray-100 hover:bg-gray-300 text-red-600 text-gray-800 py-1 px-4 rounded cursor-pointer text-sm justify-center" @click="deleteVideo(index, this)">
                                    Supprimer
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Champ d'ajout de vidéo -->

            <input :id="field.attribute" type="text" class="w-full form-control form-input form-input-bordered" :class="errorClasses" placeholder="Lien Youtube ou Vimeo" v-model="newUrl" />

            <span class="bg-primary-500 hover:bg-gray-300 text-red-600 text-center text-gray-800 py-2 px-4 rounded cursor-pointer text-sm justify-center text-white dark:text-gray-800 font-bold shadow bg-primary-500 hover:bg-primary-400 active:bg-primary-600 text-white dark:text-gray-800 block" @click="fetchVideoData">
                Ajouter
            </span>


            <!-- Champ d'erreur via JS -->
            <p v-if="error" class="my-2 text-red-600" v-html="error">
            </p>

            <!-- Champ d'erreur via PHP (pas utile normalement) -->
            <p v-if="hasError" class="my-2 text-red-600">
                {{ firstError }}
            </p>

            <!--Champ hidden qui contient le JSON final -->
            <input :id="field.attribute" type="hidden" v-model="value" />
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
            videos: [],
            newVideoData: null,
            error: null,
            newUrl: ''
        };
    },

    watch: {
        videos: {
            deep: true,
            handler(newValue, oldValue) {
                let json = JSON.stringify(newValue);
                this.value = json;
            }
        }
    },

    methods: {
        /*
         * Set the initial, internal value for the field.
         */
        setInitialValue() {
            this.value = this.field.value || '';
            this.videos = JSON.parse(this.value);
        },

        /**
         * Fill the given FormData object with the field's internal value.
         */
        fill(formData) {
            this.fillIfVisible(formData, this.fieldAttribute, this.value || '')
        },

        async fetchVideoData(event) {
            try {
                let response = await axios.get('/jolimardi/nova-video-field/fetch', {
                    params: {
                        url: this.newUrl
                    }
                });

                console.log(response.data);
                if (response.data && response.data.message) {
                    this.error = response.data.message
                    return
                }

                this.videos.push(response.data)
                this.newUrl = ""
                this.error = ''

            } catch (error) {
                if (error.response.data.message) {
                    this.error = error.response.data.message;
                    console.log("Erreur Nova Video Field : ", error.response.data);
                } else if (error.response) {
                    this.error = error.response.status;
                    console.log("Erreur Nova Video Field : ", error.response);
                } else if (error.request) {
                    console.log("Erreur Nova Video Field : ", error.request);
                } else {
                    // Something happened in setting up the request that triggered an Error
                    console.log("Erreur Nova Video Field : ", error.message);
                }
            }
        },

        updateVideoTitle(index, event) {
            this.videos[index].title = event.target.value;
        },

        deleteVideo(index) {
            this.videos.splice(index, 1);
        }
    },
}
</script>
<style>
.video {
    border: 1px solid #ddd;
    border-radius: 6px;
    overflow: hidden;
    width: 370px;
    max-width: 100%;
    margin: 16px 16px;

    img {
        aspect-ratio: 16/8;
        object-fit: contain;
    }
}
.videos-list {
    display: flex;
    flex-wrap: wrap;
}
</style>