<template>
  <div class="form-group">
    <label v-if="label" :for="forId">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>
    <slot></slot>
  </div>
</template>

<script>

export default {
  props: {
    forId: {
      type: String,
    },
    label: {
      type: String,
    },
    required: {
      type: Boolean,
      default: true
    },
  },
}
</script>
