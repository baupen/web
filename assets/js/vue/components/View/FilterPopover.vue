<template>
  <activatable-popover
      :title="title" :active="valid" :active-icon="['fas', 'filter']" :inactive-icon="['far', 'filter']"
      :size="size"
      @shown="$emit('shown')">
    <slot></slot>
  </activatable-popover>
</template>
<script>
import ActivatablePopover from "../Behaviour/ActivatablePopover";

export default {
  components: {ActivatablePopover},
  emits: ['shown'],
  props: {
    title: {
      type: String,
      required: true,
    },
    valid: {
      type: Boolean,
      required: true
    },
    size: {
      type: String,
      default: 'filter-sm'
    }
  }
}
</script>
