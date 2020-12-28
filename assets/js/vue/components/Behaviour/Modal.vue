<template>
  <div class="modal-wrapper">
    <div class="modal show fade" @click.self="$emit('hide')" id="modal" tabindex="-1" role="dialog"
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
  }
}
</script>

<style scoped="true">
.show {
  display: block;
  overflow-y: scroll;
}

</style>
