<template>
  <form-field for-id="image" :label="$t('_form.image.label')" :required="false">
    <input
        type="file"
        id="image-input"
        accept="image/jpeg"
        capture="environment"
        class="form-control"
        :class="{'is-valid': imageIsValid && dirty, 'is-invalid': !imageIsValid && dirty }"
        @change="this.image = $event.target.files[0]"
    />

    <a class="btn-link clickable" v-if="image" @click="image = null">
      {{ $t('_form.reset') }}
    </a>
  </form-field>
</template>

<script>

import FormField from '../Library/FormLayout/FormField'
import Dropzone from '../Library/FormInput/Dropzone'
import {validImageTypes} from '../../services/api'

export default {
  components: {
    Dropzone,
    FormField
  },
  emits: ['update'],
  data() {
    return {
      image: null,
      dirty: false
    }
  },
  watch: {
    image: function () {
      this.dirty = true
      this.$emit('update', this.image)
    },
  },
  computed: {
    imageIsValid: function () {
      if (!this.image) {
        return false
      }

      return this.validFileTypes.some(e => this.image.type === e)
    },
    validFileTypes: function () {
      return validImageTypes
    }
  },
  mounted() {
    this.$emit('update', null)
  }
}
</script>
