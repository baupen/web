<template>
  <form-field :for-id="id" :label="label">
    <div class="form-control form-control-dropzone" :class="{'can-drop': isDropTarget, 'is-valid': isDropTarget && validDropDetected, 'is-invalid': isDropTarget && invalidDropDetected}"
         @drag.stop.prevent="" @dragstart.stop.prevent=""
         @dragover.stop.prevent="isDropTarget = true" @dragenter.stop.prevent="dropAreaEntered"
         @dragleave.stop.prevent="isDropTarget = false" @dragend.stop.prevent="isDropTarget = false"
         @drop.stop.prevent="fileDropped"
    >
      <label class="form-control-dropzone-hint">
        {{ help }}
        <input type="file" :id="id"/>
      </label>
    </div>
  </form-field>
</template>

<script>
import FormField from "../Layout/FormField";

export default {
  emits: ['input'],
  components: {FormField},
  data() {
    return {
      isDropTarget: true,
      validDropDetected: false,
      invalidDropDetected: false
    }
  },
  props: {
    id: {
      type: String,
      required: true
    },
    label: {
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

      const file = files[0]
      const result = this.validFileTypes.some(e => file.type === e)

      this.validDropDetected = result
      this.invalidDropDetected = !result
    },
    fileDropped: function (event) {
      this.isDropTarget = false
      if (this.validDropDetected) {
        this.$emit('input', event.dataTransfer.files[0])
      }
    }
  }
}
</script>
