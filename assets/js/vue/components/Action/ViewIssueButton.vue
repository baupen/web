<template>
  <button-with-modal-confirm
      :title="$t('actions.view_issue')" :color="stateColor"
      :confirm-title="$t('actions.close')">

    <template v-slot:button-content>
      <font-awesome-icon v-if="isClosed" :icon="['far', 'check-circle']" />
      <font-awesome-icon v-else-if="isResolved" :icon="['far', 'exclamation-circle']" />
      <font-awesome-icon v-else-if="isRegistered" :icon="['far', 'dot-circle']" />
      <font-awesome-icon v-else :icon="['far', 'plus-circle']" />
    </template>

    hi mom

  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
export default {
  components: {
    ButtonWithModalConfirm
  },
  props: {
    issue: {
      type: Object,
      required: true
    },
  },
  computed: {
    stateColor: function () {
      if (this.isClosed) {
        return 'success'
      }

      if (this.isResolved) {
        return 'warning'
      }

      return 'primary'
    },
    isResolved: function () {
      return !!this.issue.resolvedBy
    },
    isClosed: function () {
      return !!this.issue.closedBy
    },
    isRegistered: function () {
      return !!this.issue.registeredBy;
    }
  }
}
</script>
