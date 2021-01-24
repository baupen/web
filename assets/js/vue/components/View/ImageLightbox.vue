<template>
  <lightbox
      v-if="src"
      :src="src" :src-full="src + '?size=full'"
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
  }

}
</script>
