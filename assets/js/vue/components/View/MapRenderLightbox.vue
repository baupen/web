<template>
  <image-lightbox :src="src" :subject="subject" :preview="preview" />
</template>

<script>
import Lightbox from '../Library/Behaviour/Lightbox'
import ImageLightbox from './ImageLightbox'
import { api, iriToId } from '../../services/api'

export default {
  components: {
    ImageLightbox,
    Lightbox
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
    craftsman: {
      type: Object,
      required: false
    },
    issue: {
      type: Object,
      required: false
    },
    state: {
      type: Number,
      required: false
    },
    query: {
      type: Object,
      default: { }
    },
    preview: {
      type: Boolean,
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

      let query = Object.assign({}, this.query)
      if (this.craftsman) {
        query['craftsman'] = iriToId(this.craftsman['@id']);
      }
      if (this.issue) {
        query['number'] = this.issue.number;
      }
      if (this.state) {
        query['state'] = this.state;
      }

      return api.getIssuesRenderLink(this.constructionSite, this.map, query)
    }
  }
}
</script>
