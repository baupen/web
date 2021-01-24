<template>
  <button-with-modal-confirm :title="$t('remove_issues_button.modal_title')" color="danger" :can-confirm="hasConfirmed"
                             :confirm-title="removeIssuesText" :button-disabled="disabled"
                             @confirm="$emit('remove')">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']"/>
    </template>

    <div>
      <custom-checkbox-field for-id="confirm-removal" :label="$t('remove_issues_button.can_not_reverse')">
        <input
            class="custom-control-input" type="checkbox" id="confirm-removal"
            v-model="hasConfirmed"
            :true-value="true"
            :false-value="false"
        >
      </custom-checkbox-field>
    </div>

  </button-with-modal-confirm>
</template>

<script>
import ButtonWithModalConfirm from "./Behaviour/ButtonWithModalConfirm";
import CustomCheckboxField from "./Edit/Layout/CustomCheckboxField";

export default {
  emits: ['remove'],
  components: {CustomCheckboxField, ButtonWithModalConfirm},
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
