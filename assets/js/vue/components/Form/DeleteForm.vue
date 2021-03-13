<template>
  <custom-checkbox-field for-id="remove-confirm" :label="$t('_action.remove_confirm')">
    <input
        class="custom-control-input" type="checkbox" id="remove-confirm"
        :class="{'is-valid': removeConfirmField.dirty && !removeConfirmField.errors.length, 'is-invalid': removeConfirmField.dirty && removeConfirmField.errors.length }"
        v-model="removeConfirm"
        :true-value="true"
        :false-value="false"
        @input="removeConfirmField.dirty = true"
        @change="validateRemoveConfirm"
    >
    <template v-slot:after>
      <invalid-feedback :errors="removeConfirmField.errors" />
    </template>
  </custom-checkbox-field>
</template>

<script>
import { createField, requiredRule, validateField } from '../../services/validation'
import CustomCheckboxField from '../Library/FormLayout/CustomCheckboxField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'

export default {
  components: {
    InvalidFeedback,
    CustomCheckboxField
  },
  emits: ['update'],
  data () {
    return {
      removeConfirmField: createField(requiredRule()),
      removeConfirm: false,
    }
  },
  watch: {
    removeConfirm: function () {
      this.$emit('update', this.removeConfirm)
    },
  },
  methods: {
    validateRemoveConfirm: function () {
      validateField(this.removeConfirmField, this.removeConfirm)
    },
  },
  mounted () {
    validateField(this.removeConfirmField, this.removeConfirm)
    this.$emit('update', this.removeConfirm)
  }
}
</script>
