<template>
  <div class="card">
    <div class="card-body">
      <template v-if="link">
        <p class="bg-light-gray p-2">
          <a :href="link" target="_blank">{{ link }}</a>
        </p>
      </template>
      <template v-else-if="aborted">
        <p>{{ this.$t('view.aborted') }}</p>
      </template>
      <template v-else>
        <p class="mb-0">{{ label }}</p>
        <div class="progress">
          <div class="progress-bar progress-bar-striped progress-bar-animated"
               :class="progressClass" role="progressbar"></div>
        </div>
        <p class="text-muted mb-0">
          <i>
            {{ $t('view.issue_report_generation_result.contains_issues', { count: queryResultSize }) }}
          </i>
        </p>
      </template>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    queryResultSize: {
      type: Number,
      required: true
    },
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
      if (this.progressLabel) {
        return this.progressLabel
      }

      return this.$t('view.pending')
    },
    progressClass: function () {
      return 'progress-' + Math.round(this.progress)
    }
  }
}
</script>
