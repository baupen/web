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

      if (this.preview) {
        return this.src + '?size=preview'
      }

      return this.src
    },
    srcFullUrl: function () {
      if (!this.src) {
        return null;
      }

      return this.src + '?size=full'
    }
  }

}
</script>
