<template>
  <div class="mb-3">
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
