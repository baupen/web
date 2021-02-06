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
    renderSrc: function () {
      return this.src + "/render.jpg";
    },
    srcUrl: function () {
      if (this.preview) {
        return this.renderSrc + '?size=preview'
      }

      return this.renderSrc
    },
    srcFullUrl: function () {
      return this.renderSrc + '?size=full'
    }
  }

}
</script>
