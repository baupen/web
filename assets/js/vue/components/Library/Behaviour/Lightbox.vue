<template>
  <div>
    <!-- thumbnail image wrapped in a link -->
    <span class="clickable" @click="open = !open" @blur="open = false">
      <img :src="src" :alt="alt">
    </span>

    <!-- lightbox container hidden with CSS -->
    <div class="lightbox clickable" v-if="open" @click="open = false">
      <span class="sizer"></span>
      <img :src="srcFull" :alt="alt">
    </div>
  </div>
</template>

<script>
export default {
  data() {
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

<style scoped="true">

.lightbox {
  /* Default to hidden */
  display: none;

  /* Overlay entire screen */
  position: fixed;
  z-index: 999;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;

  /* A bit of padding around image */
  padding: 1em;

  /* Translucent background */
  background: rgba(0, 0, 0, 0.8);
  text-align: center;
}

/* Unhide the lightbox when it's the target */
.lightbox {
  display: block;
}

.lightbox .sizer {
  display: inline-block;
  height: 100%;
  vertical-align: middle;
}

.lightbox img {
  /* Full width and height */
  max-width: 100%;
  max-height: 100%;
  vertical-align: middle;

  margin: 0 auto;
}
</style>
