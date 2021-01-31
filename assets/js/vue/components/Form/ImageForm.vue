<template>
  <form-field for-id="file" :label="$t('form.image.label')">
    <dropzone
        v-if="!file"
        id="file" :help="$t('form.image.drop_or_choose')"
        :valid-file-types="validFileTypes"
        @input="file = $event[0]" />
    <input v-if="file" id="file" class="form-control is-valid" type="text" readonly="readonly"
           :value="file.name">
    <a class="btn-link clickable" v-if="file" @click="file = null">
      {{ $t('form.image.reset') }}
    </a>
  </form-field>
</template>

<script>

import FormField from '../Library/FormLayout/FormField'
import Dropzone from '../Library/FormInput/Dropzone'
import { validImageTypes } from '../../services/api'

export default {
  components: {
    Dropzone,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      mounted: false,
      file: null
    }
  },
  watch: {
    file: function () {
      if (this.mounted) {
        this.$emit('update', this.file)
      }
    },
  },
  computed: {
    validFileTypes: function () {
      return validImageTypes;
    }
  },
  mounted () {
    this.mounted = true
    this.$emit('update', this.file)
  }
}
</script>
