<template>
  <div class="form-group">
    <custom-checkbox :for-id="forId" :label="label" :secondary-label="secondaryLabel" :required="required">
      <slot></slot>
      <template v-slot:after>
        <slot name="after"></slot>
      </template>
    </custom-checkbox>
  </div>
</template>

<script>

import CustomCheckbox from '../FormInput/CustomCheckbox'
export default {
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
  }
}
</script>
