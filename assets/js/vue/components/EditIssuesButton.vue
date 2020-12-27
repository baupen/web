<template>
  <button-with-modal-confirm :title="$t('edit_issues_button.modal_title')" color="secondary" :can-confirm="issue !== null"
                             :confirm-title="storeIssuesText" :button-disabled="disabled">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'pencil']"/>
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
      issue: null
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
    storeIssuesText: function () {
      return this.$tc('edit_issues_button.actions.save_issues', this.issues.length, {'count': this.issues.length})
    },
  }
}
</script>
