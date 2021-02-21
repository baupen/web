<template>
  <p v-if="!this.link" class="mb-0">{{ label }}</p>
  <div v-if="!this.aborted && !this.link" class="progress">
    <div class="progress-bar progress-bar-striped progress-bar-animated"
         :class="progressClass" role="progressbar"></div>
  </div>
  <p v-if="this.link" class="bg-light-gray p-2">
    <a :href="link" target="_blank">{{ link }}</a>
  </p>
</template>

<script>
export default {
  props: {
    progressLabel: {
      type: String,
      required: false
    },
    progress: {
      type: Number,
      required: true
    },
    link: {
      type: String,
      required: false
    },
    aborted: {
      type: Boolean,
      required: false
    }
  },
  computed: {
    label: function () {
      if (this.aborted) {
        return this.$t('view.aborted')
      }

      if (this.progressClass) {
        return this.progressClass
      }

      return this.$t('view.pending')
    },
    progressClass: function () {
      return 'progress-' + Math.round(this.progress)
    }
  }
}
</script>
