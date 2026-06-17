<template>
  <image-lightbox :src="src" :subject="subject" :preview="preview" :content-hash="contentHash" />
</template>

<script>
import ImageLightbox from './ImageLightbox'
import { iriToId, router } from '../../domain/api'

export default {
  components: {
    ImageLightbox,
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
    contentHash: {
      required: true
    },
    state: {
      type: Number,
      required: false
    },
    query: {
      type: Object,
      required: false
    },
    preview: {
      type: Boolean,
      default: false
    },
    empty: {
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
      if (this.empty) {
        query['number'] = -1
      }

      return router.getIssuesRenderLink(this.constructionSite, this.map, query)
    }
  }
}
</script>
