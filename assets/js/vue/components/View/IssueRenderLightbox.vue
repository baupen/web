<template>
  <image-lightbox v-if="this.issue.mapRenderUrl" :src="this.issue.mapRenderUrl" :subject="subject" :content-hash="contentHash" :preview="preview" />
  <map-render-lightbox
      v-else
      :preview="preview" :construction-site="constructionSite" :map="map" :issue="issue" :content-hash="contentHash" />
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
    contentHash: function () {
      return 'x=' + this.issue.positionX + ',y=' + this.issue.positionY
    }
  }
}
</script>
