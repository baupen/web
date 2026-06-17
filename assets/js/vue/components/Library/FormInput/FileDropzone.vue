<template>
    <div class="form-control form-control-dropzone" :class="{'can-drop': isDropTarget, 'is-valid': isDropTarget && validDropDetected, 'is-invalid': isDropTarget && invalidDropDetected}"
         @drag.stop.prevent="" @dragstart.stop.prevent=""
         @dragover.stop.prevent="isDropTarget = true" @dragenter.stop.prevent="dropAreaEntered"
         @dragleave.stop.prevent="isDropTarget = false" @dragend.stop.prevent="isDropTarget = false"
         @drop.stop.prevent="fileDropped"
    >
      <label class="form-control-dropzone-hint">
        <span>
          {{ help }}
          <span v-if="lastInvalidFileType && isDropTarget" class="text-danger d-block text-center">
            {{lastInvalidFileType}}
          </span>
        </span>
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
      invalidDropDetected: false,
      lastInvalidFileType: null
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

      let lastInvalidFileType = null;
      Array.from(files).forEach(file => {
        if (!this.validFileTypes.some(e => file.type === e)) {
          lastInvalidFileType = file.type
        }
      })

      this.validDropDetected = !lastInvalidFileType
      this.invalidDropDetected = !this.validDropDetected
      this.lastInvalidFileType = lastInvalidFileType
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
