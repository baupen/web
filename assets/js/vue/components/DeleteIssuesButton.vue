<template>
  <button-with-modal-confirm :title="$t('delete_issues_button.modal_title')" color="danger" :can-confirm="hasConfirmed"
                             :confirm-title="deleteIssuesText" :button-disabled="disabled">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']"/>
    </template>

    <div>
      {{issues}}
    </div>

  </button-with-modal-confirm>
</template>

<script>
import ButtonWithModalConfirm from "./Behaviour/ButtonWithModalConfirm";

export default {
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
    deleteIssuesText: function () {
      return this.$tc('delete_issues_button.actions.delete_issues', this.issues.length, {'count': this.issues.length})
    },
  }
}
</script>
