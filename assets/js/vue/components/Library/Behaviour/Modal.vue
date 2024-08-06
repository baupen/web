<template>
  <div class="modal-wrapper d-inline-block">
    <div class="modal show fade" @mousedown="lastMouseDownEvent = $event" @mouseup.self="mouseUpOutside" id="modal" tabindex="-1" role="dialog"
         aria-labelledby="modal-title"
         aria-hidden="true">
      <div class="modal-dialog shadow" :class="{'modal-sm': size === 'sm', 'modal-lg': size === 'lg'}" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <slot name="header">
              <h5 class="modal-title" id="modal-title">{{ title }}</h5>
            </slot>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" @click="$emit('hide')"></button>
          </div>
          <div class="modal-body">
            <slot name="body"></slot>
          </div>
          <slot name="after-body"></slot>
        </div>
      </div>
    </div>
    <div class="modal-backdrop fade show"></div>
  </div>
</template>

<script>
export default {
  emits: ['hide'],
  props: {
    title: {
      type: String,
      required: null
    },
    size: {
      type: String,
      default: null
    }
  },
  data() {
    return {
      lastMouseDownEvent: null
    }
  },
  methods: {
    mouseUpOutside: function (event) {
      if (!this.lastMouseDownEvent) {
        this.$emit('hide')
        return
      }

      const diffX = Math.abs(event.pageX - this.lastMouseDownEvent.pageX);
      const diffY = Math.abs(event.pageY - this.lastMouseDownEvent.pageY);

      if (diffX < 10 && diffY < 10) {
        this.$emit('hide')
      }
    }
  }
}
</script>

<style scoped>
.show {
  display: block;
  overflow-y: scroll;
}

</style>
