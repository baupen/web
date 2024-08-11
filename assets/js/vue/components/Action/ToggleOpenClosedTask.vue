<template>
  <div @click="toggleClosed">
    <input class="form-check-input" type="checkbox" :checked="task.closedAt" :disabled="isPatching">
  </div>
</template>

<script>

import { api } from '../../services/api'

export default {
  props: {
    task: {
      type: Object,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: true
    }
  },
  data() {
    return {
      isPatching: false
    }
  },
  computed: {
    isClosed: function () {
      return !!this.task.closedAt
    }
  },
  methods: {
    toggleClosed: function () {
      this.isPatching = true
      if (this.isClosed) {
        const patch = { 'closedAt': null, 'closedBy': null }
        api.patch(this.task, patch, this.$t('_action.toggle_open_closed_task.opened')).then(_ => this.isPatching = false)
      } else {
        const patch = { 'closedAt': (new Date()).toISOString(), 'closedBy': this.constructionManagerIri }
        api.patch(this.task, patch, this.$t('_action.toggle_open_closed_task.closed')).then(_ => this.isPatching = false)
      }
    }
  }
}
</script>
