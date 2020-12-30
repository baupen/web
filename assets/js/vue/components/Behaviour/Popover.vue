<template>
  <div>
    <span class="clickable" ref="button" aria-describedby="tooltip" @click.prevent.stop="activated = !activated" @mouseenter="hoveredButton" @mouseleave="unHoveredButton">
      <slot name="button"/>
    </span>
    <div ref="tooltip" class="popover fade" :show="show" :class="{'show': show}" @mouseenter="hoveredContent" v-click-outside="clickOutside" role="tooltip">
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
  emits: ['shown'],
  data() {
    return {
      instance: null,
      activated: false,
      hover: false,
      postButtonHover: false
    }
  },
  props: {
    title: {
      type: String,
      required: true,
    }
  },
  watch: {
    show: function () {
      if (this.show) {
        this.$emit('shown')
      }
    }
  },
  methods: {
    clickOutside: function () {
      this.activated = false
    },
    hoveredButton: function () {
      this.hover = true;
      this.postButtonHover = false
    },
    hoveredContent: function () {
      if (this.postButtonHover) {
        this.activated = true;
      }
    },
    unHoveredButton: function () {
      this.postButtonHover = true;
      setTimeout(()=>{
        if (this.postButtonHover) {
          this.hover = false;
          this.postButtonHover = false;
        }
      },300);
    }
  },
  computed: {
    show: function () {
      return this.activated || this.hover
    }
  },
  mounted() {
    const button = this.$refs.button;
    const tooltip = this.$refs.tooltip;

    this.instance = createPopper(button, tooltip, {
      placement: 'top',
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
