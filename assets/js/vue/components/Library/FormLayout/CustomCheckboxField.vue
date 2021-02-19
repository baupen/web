<template>
  <div class="form-group">
    <custom-checkbox :for-id="forId" :label="label" :secondary-label="secondaryLabel" :required="required">
      <slot></slot>
      <template v-slot:after>
        <div v-if="showReset">
          <a class="btn-link clickable" @click="$emit('reset')">
            {{ $t('form.reset') }}
          </a>
        </div>
        <slot name="after"></slot>
      </template>
    </custom-checkbox>
  </div>
</template>

<script>

import CustomCheckbox from '../FormInput/CustomCheckbox'
export default {
  emits: ['reset'],
  components: {
    CustomCheckbox
  },
  props: {
    forId: {
      type: String,
      required: false
    },
    label: {
      type: String,
      required: false
    },
    secondaryLabel: {
      type: String,
      required: false
    },
    required: {
      type: Boolean,
      default: false
    },
    showReset: {
      type: Boolean,
      default: false
    }
  }
}
</script>
