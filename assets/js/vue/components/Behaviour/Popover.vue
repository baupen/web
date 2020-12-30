<template>
  <div>
    <span class="clickable" ref="button" aria-describedby="tooltip" @click.prevent.stop="show = !show">
      <slot name="button"/>
    </span>
    <div ref="tooltip" class="popover fade bs-popover-right" :show="show" :class="{'show': show}" v-click-outside="clickOutside" role="tooltip">
      <div class="arrow" data-popper-arrow></div>
      <h3 class="popover-header">{{ title }}</h3>
      <div class="popover-body" @click.stop="">
        <slot></slot>
      </div>
    </div>
  </div>
</template>

<script>
import {createPopper} from '@popperjs/core';

export default {
  data() {
    return {
      instance: null,
      show: false
    }
  },
  props: {
    title: {
      type: String,
      required: true,
    }
  },
  methods: {
    clickOutside: function () {
      this.show = false
    }
  },
  mounted() {
    const button = this.$refs.button;
    const tooltip = this.$refs.tooltip;

    this.instance = createPopper(button, tooltip, {
      placement: 'right',
      modifiers: [
        {
          name: 'offset',
          options: {
            offset: [0, 8],
          },
        },
      ],
    });
  },
  unmounted() {
    this.instance.destroy();
    this.instance = null;
  }
}
</script>

<style scoped="true">
.tooltip {
  background-color: #333;
  color: white;
  padding: 5px 10px;
  border-radius: 4px;
  font-size: 13px;
}
</style>
