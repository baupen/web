<template>
  <div class="custom-control custom-checkbox">
    <slot></slot>
    <label class="custom-control-label" :for="forId">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>
  </div>
</template>

<script>

export default {
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
