<template>
  <p class="alert alert-info" v-if="!file">
    {{ $t('_form.craftsmen_import.template_help') }} <br />
    <a :href="downloadExcelTemplateHref" :download="filename">
      {{ $t('_form.craftsmen_import.template_download') }}
    </a>
  </p>
  <form-field for-id="file" :label="$t('_form.craftsmen_import.file')">
    <dropzone
        v-if="!file"
        id="file" :help="dropHelp"
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

import { displayError } from '../../services/notifiers'

import FormField from '../Library/FormLayout/FormField'
import Dropzone from '../Library/FormInput/Dropzone'
import { craftsmanTransformer, excelTransformer } from '../../services/transformers'
import {isSafari} from "../../services/utils";

export default {
  components: {
    Dropzone,
    FormField
  },
  emits: ['imported'],
  data () {
    return {
      file: null
    }
  },
  props: {
    craftsmen: {
      type: Array,
      required: false
    }
  },
  watch: {
    file: function () {
      if (!this.file) {
        this.$emit('imported', null)
        return
      }

      const reader = new FileReader()
      reader.onload = () => {
        this.parseExcelFile(reader.result)
      }
      reader.readAsArrayBuffer(this.file)
    }
  },
  methods: {
    parseExcelFile: function (arrayBuffer) {
      const craftsmen = craftsmanTransformer.importExcel(arrayBuffer)

      let errorsShown = 0;
      for (let i = 0; i < craftsmen.length; i++) {
        const craftsman = craftsmen[i]

        if ((!craftsman.trade || !craftsman.company || !craftsman.contactName || !craftsman.email) && errorsShown < 3) {
          displayError(this.$t('_form.craftsmen_import.invalid_entry', { 'line': i + 1 }))
          errorsShown++
        }
      }

      if (errorsShown === 0) {
        this.$emit('imported', craftsmen)
      }
    },
  },
  computed: {
    validFileTypes: function () {
      return excelTransformer.getImportMimeTypes()
    },
    filename: function () {
      return this.$t('_form.craftsmen_import.template_file_name') + ".xlsx"
    },
    fileIsValid: function () {
      if (!this.file) {
        return false
      }

      return this.validFileTypes.some(e => this.file.type === e)
    },
    downloadExcelTemplateHref: function () {
      const blob = craftsmanTransformer.importExcelTemplate(this.$t)
      return window.URL.createObjectURL(blob)
    },
    dropHelp: function () {
      return isSafari ? this.$t('_form.craftsmen_import.file_drop_or_choose') : this.$t('_form.craftsmen_import.file_choose')
    }
  },
  mounted () {
    this.$emit('imported', null)
  }
}
</script>
