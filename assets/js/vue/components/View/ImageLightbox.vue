<template>
  <lightbox
      v-if="src"
      :src="srcUrl" :src-full="srcFullUrl"
      :alt="'thumbnail of ' + subject" />
</template>

<script>
import Lightbox from '../Library/Behaviour/Lightbox'

export default {
  components: {
    Lightbox
  },
  props: {
    src: {
      type: String,
      required: false
    },
    subject: {
      required: true
    },
    contentHash: {
      type: String,
      required: false
    },
    preview: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    srcUrl: function () {
      if (!this.src) {
        return null;
      }

      let url = new URL(this.src, window.location.origin);

      if (this.preview) {
        url.searchParams.set('size', 'preview');
        if (this.contentHash) {
          url.searchParams.set('ch', this.contentHash);
        }
      }

      return url.toString()
    },
    srcFullUrl: function () {
      let url = new URL(this.src, window.location.origin);
      url.searchParams.set('size', 'full')
      if (this.contentHash) {
        url.searchParams.set('ch', this.contentHash);
      }
      return url.toString()
    }
  }
}
</script>
