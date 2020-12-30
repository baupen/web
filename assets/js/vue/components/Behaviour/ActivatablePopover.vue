<template>
  <popover :title="title" @shown="$emit('shown')">
    <slot></slot>
    <template v-slot:button>
      <span>
        <font-awesome-icon v-if="active" :class="{'text-primary': active}" :icon="activeIcon"/>
        <font-awesome-icon v-else :class="{'text-primary': active}" :icon="inactiveIcon"/>
      </span>
    </template>
  </popover>
</template>

<script>

import Popover from "./Popover";

export default {
  components: {Popover},
  emits: ['shown'],
  props: {
    title: {
      type: String,
      required: true,
    },
    activeIcon: {
      type: Array,
      required: true
    },
    inactiveIcon: {
      type: Array,
      required: true
    },
    active: {
      type: Boolean,
      required: true
    }
  },
  computed: {
    icon: function () {
      return this.active ? this.inactiveIcon : this.activeIcon
    },
  },
}
</script>
