<template>
  <div>
    <custom-radio-field
        for-id="issue-event-type-text"
        :label="$t('_action.add_issue_event.type_text')">
      <input
          id="issue-event-type-text" class="form-check-input" type="radio"
          name="issue-event-type" value="TEXT"
          v-model="value">
    </custom-radio-field>

    <custom-radio-field
        for-id="issue-event-type-image"
        :label="$t('_action.add_issue_event.type_image')">
      <input
          id="issue-event-type-image" class="form-check-input" type="radio"
          name="issue-event-type" value="IMAGE"
          v-model="value">
    </custom-radio-field>

    <custom-radio-field
        for-id="issue-event-type-file"
        :label="$t('_action.add_issue_event.type_file')">
      <input
          id="issue-event-type-file" class="form-check-input" type="radio"
          name="issue-event-type" value="FILE"
          v-model="value">
    </custom-radio-field>
  </div>
</template>

<script>

import CustomRadioField from "../../Library/FormLayout/CustomRadioField.vue";

export default {
  components: {CustomRadioField},
  props: ['modelValue'],
  emits: ['update:modelValue'],
  computed: {
    value: {
      get() {
        return this.modelValue
      },
      set(value) {
        this.$emit('update:modelValue', value)
      }
    }
  }
}
</script>
