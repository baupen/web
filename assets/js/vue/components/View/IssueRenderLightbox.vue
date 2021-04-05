<template>
  <image-lightbox v-if="this.issue.renderUrl" :src="this.issue.renderUrl" :subject="subject" :preview="preview" />
  <map-render-lightbox
      v-else
      :preview="preview" :construction-site="constructionSite" :map="map" :issue="issue" />
</template>

<script>
import ImageLightbox from './ImageLightbox'
import MapRenderLightbox from './MapRenderLightbox'

export default {
  components: {
    MapRenderLightbox,
    ImageLightbox
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    map: {
      type: Object,
      required: true
    },
    issue: {
      type: Object,
      required: false
    },
    preview: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    subject: function () {
      return this.map.name + ': ' + this.issue.number
    },
  }
}
</script>
