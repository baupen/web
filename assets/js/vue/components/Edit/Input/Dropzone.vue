<template>
    <div class="form-control form-control-dropzone" :class="{'can-drop': isDropTarget, 'is-valid': isDropTarget && validDropDetected, 'is-invalid': isDropTarget && invalidDropDetected}"
         @drag.stop.prevent="" @dragstart.stop.prevent=""
         @dragover.stop.prevent="isDropTarget = true" @dragenter.stop.prevent="dropAreaEntered"
         @dragleave.stop.prevent="isDropTarget = false" @dragend.stop.prevent="isDropTarget = false"
         @drop.stop.prevent="fileDropped"
    >
      <label class="form-control-dropzone-hint">
        {{ help }}
        <input type="file" :id="id" @change="$emit('input', $event.target.files)"/>
      </label>
    </div>
</template>

<script>

export default {
  emits: ['input'],
  data() {
    return {
      isDropTarget: false,
      validDropDetected: false,
      invalidDropDetected: false
    }
  },
  props: {
    id: {
      type: String,
      required: true
    },
    help: {
      type: String,
      required: true
    },
    validFileTypes: {
      type: Array,
      required: true
    }
  },
  methods: {
    dropAreaEntered: function (event) {
      this.isDropTarget = true
      const files = event.dataTransfer.items;
      if (files.length === 0) {
        return
      }

      const result = Array.from(files).every(file => this.validFileTypes.some(e => file.type === e))

      this.validDropDetected = result
      this.invalidDropDetected = !result
    },
    fileDropped: function (event) {
      this.isDropTarget = false
      if (this.validDropDetected) {
        this.$emit('input', event.dataTransfer.files)
      }
    }
  }
}
</script>
