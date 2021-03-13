<template>
  <p class="alert alert-info" v-if="!file">
    {{ $t('_form.craftsmen_import.template_help') }} <br />
    <a :href="downloadSampleExcelHref" :download="$t('_form.craftsmen_import.template_file_name')">
      {{ $t('_form.craftsmen_import.template_download') }}
    </a>
  </p>
  <form-field for-id="file" :label="$t('_form.craftsmen_import.file')">
    <dropzone
        v-if="!file"
        id="file" :help="$t('_form.craftsmen_import.file_drop_or_choose')"
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

const header = ['trade', 'company', 'contact_name', 'email', 'emailCCs']
const defaultContent = [
  ['Web', 'mangel.io', 'Florian Moser', 'f@mangel.io', 'info@mangel.io, support@mangel.io'],
  ['iOS', 'mangel.io', 'Julian Dunskus', 'j@mangel.io', 'info@mangel.io, support@mangel.io'],
]
const xlsxMimeType = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
const xlsMimeType = 'application/vnd.ms-excel'

import FormField from '../Library/FormLayout/FormField'
import Dropzone from '../Library/FormInput/Dropzone'
import XLSX from 'xlsx'

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
    parseExcelFile: function (fileContent) {
      const data = new Uint8Array(fileContent);
      const workbook = XLSX.read(data, {type: 'array'});
      const worksheet = workbook.Sheets[workbook.SheetNames[0]]
      console.log(worksheet)
      const content = XLSX.utils.sheet_to_json(worksheet, {header:1})
      console.log(content)

      let craftsmen = []
      let valid = true
      for (let i = 1; i < content.length; i++) {
        let entry = content[i]

        let craftsman = {
          trade: entry[0],
          company: entry[1],
          contactName: entry[2],
          email: entry[3],
          emailCCs: []
        }

        if (!craftsman.trade || !craftsman.company || !craftsman.contactName || !craftsman.email) {
          displayError(this.$t('_form.craftsman_import.invalid_entry', { 'line': i + 1 }))
          valid = false
        }

        if (entry[4]) {
          craftsman.emailCCs = entry[4].split(',').map(e => e.trim()).filter(e => e)
        }

        craftsmen.push(craftsman)
      }

      if (valid) {
        this.$emit('imported', craftsmen)
      }
    },
  },
  computed: {
    validFileTypes: function () {
      return [xlsxMimeType, xlsMimeType]
    },
    fileIsValid: function () {
      if (!this.file) {
        return false
      }

      return this.validFileTypes.some(e => this.file.type === e)
    },
    downloadSampleExcelHref: function () {
      const blob = new Blob([this.sampleExcelString], { type: xlsxMimeType })
      return window.URL.createObjectURL(blob)
    },
    sampleExcelString: function () {
      const translatedHeader = header.map(h => this.$t('craftsman.' + h))
      translatedHeader[translatedHeader.length - 1] += ' (' + this.$t('_form.craftsman_import.emailCCs_format') + ')'
      let content = [translatedHeader, ...defaultContent]
      if (this.craftsmen) {
        content = [translatedHeader, ...this.craftsmen.map(c => [c.trade, c.company, c.contactName, c.email, c.emailCCs.join(', ')])]
      }

      let workbook = XLSX.utils.book_new()
      const worksheet = XLSX.utils.aoa_to_sheet(content)
      XLSX.utils.book_append_sheet(workbook, worksheet, this.$t('craftsman._plural'))

      return XLSX.write(workbook, {
        bookType: 'xlsx',
        bookSST: false,
        type: 'array'
      })
    }
  }
}
</script>
