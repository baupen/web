<template>
  <button-with-modal-confirm :title="$t('remove_issues_button.modal_title')" color="danger" :can-confirm="hasConfirmed"
                             :confirm-title="removeIssuesText" :button-disabled="disabled"
                             @confirm="$emit('remove')">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']"/>
    </template>

    <div>
      {{ issues }}
    </div>

  </button-with-modal-confirm>
</template>

<script>
import ButtonWithModalConfirm from "./Behaviour/ButtonWithModalConfirm";

export default {
  emits: ['remove'],
  components: {ButtonWithModalConfirm},
  data() {
    return {
      hasConfirmed: false
    }
  },
  props: {
    issues: {
      type: Array,
      required: true
    },
    disabled: {
      type: Boolean,
      required: true
    }
  },
  computed: {
    removeIssuesText: function () {
      return this.$tc('remove_issues_button.actions.remove_issues', this.issues.length, {'count': this.issues.length})
    },
  }
}
</script>
