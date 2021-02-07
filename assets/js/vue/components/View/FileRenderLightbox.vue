<template>
  <image-lightbox :src="renderUrl" :subject="subject" :preview="preview" />
</template>

<script>
import Lightbox from '../Library/Behaviour/Lightbox'
import ImageLightbox from './ImageLightbox'

export default {
  components: {
    ImageLightbox,
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
      default: false
    }
  },
  computed: {
    renderUrl: function () {
      return this.src ? this.src + '/render.jpg' : null;
    }
  }
}
</script>
