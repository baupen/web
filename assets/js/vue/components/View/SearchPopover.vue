<template>
  <activatable-popover :title="title" :size="size" :active="valid" :active-icon="['fas', 'search']" :inactive-icon="['far', 'search']" @shown="$emit('shown')">
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
