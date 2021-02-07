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
      }

      return url.toString()
    },
    srcFullUrl: function () {
      let url = new URL(this.src, window.location.origin);
      url.searchParams.set('size', 'full')
      return url.toString()
    }
  }
}
</script>
