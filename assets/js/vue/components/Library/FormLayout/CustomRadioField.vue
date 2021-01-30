<template>
  <div class="form-group">
    <custom-radio :for-id="forId" :label="label" :secondary-label="secondaryLabel" :required="required">
      <slot></slot>
    </custom-radio>
    <slot name="after"></slot>
  </div>
</template>

<script>

import CustomRadio from "../FormInput/CustomRadio";
export default {
  components: {CustomRadio,},
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
