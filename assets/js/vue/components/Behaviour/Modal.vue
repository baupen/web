<template>
  <div class="modal-wrapper">
    <div class="modal show fade" @mousedown="lastMouseDownEvent = $event" @mouseup.self="mouseUpOutside" id="modal" tabindex="-1" role="dialog"
         aria-labelledby="modal-title"
         aria-hidden="true">
      <div class="modal-dialog shadow" :class="{'modal-sm': size === 'sm', 'modal-lg': size === 'lg'}" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="modal-title">{{ title }}</h5>
            <button type="button" class="close" aria-label="Close" @click="$emit('hide')">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <slot name="modal-body"></slot>
          </div>
          <div class="modal-footer">
            <slot name="modal-footer"></slot>
          </div>
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
      required: true
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

<style scoped="true">
.show {
  display: block;
  overflow-y: scroll;
}

</style>
