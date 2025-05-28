<template>
  <custom-checkbox for-id="mark-closed" :label="$t('_action.mark_issue_closed.mark')">
    <input
        class="form-check-input" type="checkbox" id="mark-closed"
        v-model="isclosed"
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
    constructionManagerIri: {
      type: String,
      required: true
    },
  },
  computed: {
    isclosed: function () {
      return !!this.issue.closedAt
    }
  },
  methods: {
    toggle() {
      let patch = {}
      patch['closedAt'] = this.isclosed ? null : (new Date()).toISOString()
      patch['closedBy'] = this.isclosed ? null : this.constructionManagerIri
      this.patching = true

      api.patch(this.issue, patch)
          .then(_ => {
            this.patching = false
            displaySuccess(this.$t('_action.mark_issue_closed.marked'))
            this.$emit('marked')
          })
    }
  },
}
</script>
