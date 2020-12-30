<template>
  <span class="clickable d-inline-block" ref="button" aria-describedby="tooltip" @click.prevent.stop="focus = !focus"
        @mouseenter="hoveredButton" @mouseleave="unHoveredButton">
    <slot name="button"/>
  </span>
  <div ref="tooltip" class="popover fade" role="tooltip" v-click-outside="clickOutside"
       :show="show" :class="{'show': show}"
       @mouseenter="hoveredContent">
    <div class="arrow" data-popper-arrow></div>
    <h3 class="popover-header">{{ title }}</h3>
    <div class="popover-body popover-body-size" @click.stop="">
      <slot></slot>
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
      focus: false,
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
        this.instance.update()
        this.$emit('shown')
      }
    }
  },
  methods: {
    clickOutside: function () {
      this.focus = false
    },
    hoveredButton: function () {
      this.hover = true;
      this.postButtonHover = false
    },
    hoveredContent: function () {
      if (this.postButtonHover) {
        this.focus = true;
      }
    },
    unHoveredButton: function () {
      this.postButtonHover = true;
      setTimeout(() => {
        if (this.postButtonHover) {
          this.hover = false;
          this.postButtonHover = false;
        }
      }, 300);
    }
  },
  computed: {
    show: function () {
      return this.focus || this.hover
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
