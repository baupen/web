<template>
  <custom-checkbox for-id="mark-resolved" :label="$t('_action.mark_issue_resolved.mark')">
    <input
        class="form-check-input" type="checkbox" id="mark-resolved"
        v-model="isResolved"
        :true-value="true"
        :false-value="false"
        @click="toggle"
        :disabled="patching">
  </custom-checkbox>
</template>

<script>

import {api} from '../../services/api'
import {displaySuccess} from '../../services/notifiers'
import CustomCheckbox from "../Library/FormInput/CustomCheckbox.vue";

export default {
  emits: ['marked'],
  components: {CustomCheckbox},
  data() {
    return {
      patching: null,
    }
  },
  props: {
    issue: {
      type: Object,
      required: true
    },
  },
  computed: {
    isResolved: function () {
      return !!this.issue.resolvedAt
    }
  },
  methods: {
    toggle() {
      let patch = {}
      patch['resolvedAt'] = this.isResolved ? null : (new Date()).toISOString()
      patch['resolvedBy'] = this.isResolved ? null : this.issue.craftsman
      this.patching = true

      api.patch(this.issue, patch)
          .then(_ => {
            this.patching = false
            displaySuccess(this.$t('_action.mark_issue_resolved.marked'))
            this.$emit('marked')
          })
    }
  },
}
</script>
