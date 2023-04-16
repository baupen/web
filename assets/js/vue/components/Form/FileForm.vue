<template>
  <form-field for-id="file" :label="$t('_form.file.label')" :required="false">
    <dropzone
        v-if="!file"
        id="file" :help="$t('_form.file.drop_or_choose')"
        :valid-file-types="validFileTypes"
        @input="file = $event[0]" />
    <input v-if="file" id="file" class="form-control" type="text" readonly="readonly"
           :class="{'is-valid': fileIsValid, 'is-invalid': !fileIsValid && file !== null }"
           :value="file.name">
    <a class="btn-link clickable" v-if="file" @click="file = null">
      {{ $t('_form.reset') }}
    </a>
  </form-field>
</template>

<script>

import FormField from '../Library/FormLayout/FormField'
import Dropzone from '../Library/FormInput/Dropzone'
import { validFileTypes } from '../../services/api'

export default {
  components: {
    Dropzone,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      file: null
    }
  },
  watch: {
    file: function () {
      this.$emit('update', this.fileIsValid ? this.file : null)
    },
  },
  computed: {
    fileIsValid: function () {
      if (!this.file) {
        return false
      }

      return this.validFileTypes.some(e => this.file.type === e)
    },
    validFileTypes: function () {
      return validFileTypes
    }
  },
  mounted () {
    this.$emit('update', null)
  }
}
</script>
