<template>
  <span class="clickable"
        @click="toggleReceiveWeekly">
      <a class="nav-link d-inline-block" href="#">
        {{ $t('_action.toggle_receive_weekly.weekly') }}
      </a>
      <button type="button" class="btn btn-toggle"
              :class="{'active': constructionManager.receiveWeekly}">
      <div class="handle"></div>
    </button>
  </span>
</template>

<script>

import { api } from '../../services/api'

export default {
  props: {
    constructionManager: {
      type: Object,
      required: true
    }
  },
  methods: {
    toggleReceiveWeekly: function () {
      const patch = { 'receiveWeekly': !this.constructionManager.receiveWeekly }
      const message = this.constructionManager.receiveWeekly ? this.$t('_action.toggle_receive_weekly.no_weekly_anymore') : this.$t('_action.toggle_receive_weekly.weekly_subscribed')
      api.patch(this.constructionManager, patch, message)
    }
  }
}
</script>
