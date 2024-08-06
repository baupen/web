<template>
  <div>
    <!-- thumbnail image wrapped in a link -->
    <span class="lightbox-thumbnail clickable" @click="open = !open" @blur="open = false">
      <img class="img-fluid" :src="src" :alt="alt">
    </span>

    <!-- lightbox container hidden with CSS -->
    <div class="lightbox clickable" v-if="open" @click="open = false">
      <span class="sizer"></span>
      <div class="close-wrapper">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <img :src="srcFull" :alt="alt">
    </div>
  </div>
</template>

<script>
export default {
  data () {
    return {
      open: false
    }
  },
  props: {
    src: {
      type: String,
      required: true
    },
    srcFull: {
      type: String,
      required: true
    },
    alt: {
      type: String,
      required: true
    }
  }
}
</script>
