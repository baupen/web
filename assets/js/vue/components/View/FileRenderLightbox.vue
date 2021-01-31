<template>
  <lightbox
      v-if="src"
      :src="renderSrc" :src-full="renderSrc + '?size=full'"
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
    }
  },
  computed: {
    renderSrc: function () {
      return this.src + "/render.jpg";
    }
  }

}
</script>
