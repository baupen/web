<template>
  <p v-if="issue.resolvedAt" class="alert alert-success">
    {{ this.$t('actions.messages.success.issue_resolved') }}
  </p>
  <button v-else class="btn btn-primary" :disabled="isLoading" @click="resolve">
    {{ this.$t('actions.resolve_issue') }}
  </button>
</template>

<script>
import ButtonWithModal from '../Library/Behaviour/ButtonWithModal'
import { api } from '../../services/api'

export default {
  components: { ButtonWithModal },
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
    craftsmanIri: {
      type: String,
      required: true
    }
  },
  methods: {
    resolve () {
      this.isLoading = true

      const patch = {
        resolvedBy: this.craftsmanIri,
        resolvedAt: (new Date()).toISOString()
      }

      api.patch(this.issue, patch, this.$t('actions.messages.success.issue_resolved'))
          .then(_ => {
            this.isLoading = false
          })
    }
  }
}
</script>
