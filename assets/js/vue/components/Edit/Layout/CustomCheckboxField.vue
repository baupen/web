<template>
  <div class="form-group">
    <custom-checkbox :for-id="forId" :label="label" :required="required">
      <slot></slot>
    </custom-checkbox>
    <slot name="after"></slot>
  </div>
</template>

<script>

import CustomCheckbox from '../Input/CustomCheckbox'
export default {
  components: { CustomCheckbox },
  props: {
    forId: {
      type: String,
      required: false
    },
    label: {
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
