<template>
  <image-lightbox :src="src" :subject="subject" :preview="preview" />
</template>

<script>
import Lightbox from '../Library/Behaviour/Lightbox'
import ImageLightbox from './ImageLightbox'
import { iriToId } from '../../services/api'

export default {
  components: {
    ImageLightbox,
    Lightbox
  },
  props: {
    map: {
      type: Object,
      required: true
    },
    issues: {
      type: Array,
      default: []
    },
    preview: {
      default: false
    }
  },
  computed: {
    subject: function () {
      return this.map.name;
    },
    src: function () {
      if (!this.map.fileUrl) {
        return null
      }

      let url = this.map.fileUrl + '/render.jpg'

      const ids = this.issues.map(i => iriToId(i['@id']))
      if (ids.length) {
        url += "?issues[]=" + ids.join("&issues[]=")
      }

      return url
    }
  }
}
</script>
