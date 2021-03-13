<template>
  <p v-if="issue.resolvedAt" class="alert alert-success m-0">
    {{ this.$t('_action.messages.success.issue_resolved') }}
  </p>
  <button v-else class="btn btn-primary" :disabled="isLoading" @click="resolve">
    {{ this.$t('_action.resolve_issue') }}
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
