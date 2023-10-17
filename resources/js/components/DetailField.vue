<template>
    <PanelItem :index="index" :field="fieldComputed" />
</template>

<script>
export default {
    props: ['index', 'resource', 'resourceName', 'resourceId', 'field'],

    computed: {
        fieldComputed: function () {
            let computedField = this.field;
            computedField.asHtml = true;
            let videos = JSON.parse(this.field.value);
            let html = '<div class="videos-display">';
            videos.forEach(video => {
                html +=
                    '<div class="video">' +
                    '<img src="' + video.thumbnail_url + '">' +
                    '<p class="py-2 px-3">' + video.title + '</p>' +
                    '</div>'
            });
            html += "</div>";
            computedField.value = html;
            return computedField;
        }
    },

}
</script>
<style>
.videos-display {
    display: flex;
    flex-wrap: wrap;

    .video {
        width: 260px;
        margin: 8px 8px;
    }
    .video img {
        object-fit: cover;
        aspect-ratio: 16/8;
        width: 100%;
    }
}
</style>