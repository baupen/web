<template>
  <div class="mb-3 row">
    <label :for="forId" :class="'pt-2 col-sm-' + labelSize">
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
    </label>
    <div :class="'col-sm-' + (12-labelSize)">
      <slot></slot>
    </div>
  </div>
</template>

<script>

export default {
  props: {
    forId: {
      type: String,
      required: true
    },
    label: {
      type: String,
      required: true
    },
    labelSize: {
      type: Number,
      default: 2
    },
    required: {
      type: Boolean,
      default: true
    },
  }
}
</script>
