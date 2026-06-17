<template>
  <p v-if="issue.resolvedAt" class="alert alert-success m-0">
    {{ this.$t('_action.resolve_issue.resolved') }}
  </p>
  <button v-else class="btn btn-primary" :disabled="isLoading" @click="resolve">
    {{ this.$t('_action.resolve_issue.title') }}
  </button>
</template>

<script>
import { api } from '../../domain/api'

export default {
  data () {
    return {
      isLoading: false
    }
  },
  props: {
    issue: {
      type: Object,
      required: true
    },
    craftsman: {
      type: Object,
      required: true
    }
  },
  methods: {
    resolve () {
      this.isLoading = true

      const patch = {
        resolvedBy: this.craftsman['@id'],
        resolvedAt: (new Date()).toISOString()
      }

      api.patch(this.issue, patch)
          .then(_ => {
            this.isLoading = false
          })
    }
  }
}
</script>
