<template>
  <div class="custom-control custom-checkbox">
    <slot></slot>
    <label class="custom-control-label" :for="forId">
      <font-awesome-icon v-if="labelIcon && (!labelIconChecked || !checked)" :icon="labelIcon" />
      <font-awesome-icon v-if="labelIconChecked && checked" :icon="labelIconChecked" />
      {{ label }}
      <span v-if="required" class="text-danger">*</span>
      <span v-if="secondaryLabel" class="text-muted"><br/>{{ secondaryLabel }}</span>
    </label>
    <slot name="after"></slot>
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
    labelIcon: {
      type: Array,
      required: false
    },
    labelIconChecked: {
      type: Array,
      required: false
    },
    checked: {
      type: Boolean,
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
