<template>
  <div class="form-check">
    <slot></slot>
    <label class="form-check-label" :for="forId">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
      <span v-if="secondaryLabel" class="text-muted"><br/>{{ secondaryLabel }}</span>
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
