<template>
  <form-field for-id="file" :label="pdfMode ? $t('_form.file.pdf_label') : $t('_form.file.label')" :required="false">
    <dropzone
        v-if="!file"
        id="file" :help="dropHelp"
        :valid-file-types="validFileTypes"
        @input="file = $event[0]" />
    <input v-if="file" id="file" class="form-control" type="text" readonly="readonly"
           :class="{'is-valid': fileIsValid, 'is-invalid': !fileIsValid }"
           :value="file.name">
    <a class="btn-link clickable" v-if="file" @click="file = null">
      {{ $t('_form.reset') }}
    </a>
  </form-field>
</template>

<script>

import FormField from '../Library/FormLayout/FormField'
import Dropzone from '../Library/FormInput/Dropzone'
import {validPdfFileTypes, validSafeFileTypes} from '../../services/api'
import {isSafari} from "../../services/utils";

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
  props: {
    pdfMode: {
      type: Boolean,
      default: false
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

      // be permissive here, server will filter
      if (this.file.type === '') {
        return  true;
      }

      return this.validFileTypes.some(e => this.file.type === e)
    },
    validFileTypes: function () {
      if (this.pdfMode) {
        return validPdfFileTypes
      } else {
        return validSafeFileTypes
      }
    },
    dropHelp: function () {
      if (isSafari) {
        return this.pdfMode ? this.$t('_form.file.pdf_choose') : this.$t('_form.file.choose')
      } else {
        return this.pdfMode ? this.$t('_form.file.pdf_drop_or_choose') : this.$t('_form.file.drop_or_choose')
      }
    }
  },
  mounted () {
    this.$emit('update', null)
  }
}
</script>
