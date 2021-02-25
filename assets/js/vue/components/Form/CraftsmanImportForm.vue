<template>
  <p class="alert alert-info" v-if="!file">
    {{$t('import_craftsmen.template_help')}} <br/>
    <a :href="downloadSampleCSVHref" :download="$t('import_craftsmen.template_file_name')">
      {{$t('import_craftsmen.template_download')}}
    </a>
  </p>
  <form-field for-id="file" :label="$t('import_craftsmen.file')">
    <dropzone
        v-if="!file"
        id="file" :help="$t('import_craftsmen.file_drop_or_choose')"
        :valid-file-types="validFileTypes"
        @input="file = $event[0]" />
    <input v-if="file" id="file" class="form-control" type="text" readonly="readonly"
           :class="{'is-valid': fileIsValid, 'is-invalid': !fileIsValid && file !== null }"
           :value="file.name">
    <a class="btn-link clickable" v-if="file" @click="file = null">
      {{ $t('import_craftsmen.reset') }}
    </a>
  </form-field>
</template>

<script>

const header = ["trade", "company", "contact_name", "email", "emailCCs"];
const defaultContent = [
  ["Web", "mangel.io", "Florian Moser", "f@mangel.io", "info@mangel.io, support@mangel.io"],
  ["iOS", "mangel.io", "Julian Dunskus", "j@mangel.io", "info@mangel.io, support@mangel.io"],
]

import { parse, stringify } from '@vanillaes/csv';
import FormField from '../Library/FormLayout/FormField'
import Dropzone from '../Library/FormInput/Dropzone'
export default {
  components: { Dropzone, FormField },
  emits: ['imported'],
  data() {
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
    file: function (){
      if (!this.file) {
        this.$emit('imported', null)
        return
      }

      const reader = new FileReader();
      reader.readAsText(this.file);
      reader.onload = () => {
        this.parseCSVFile(reader.result);
      };
    }
  },
  methods: {
    parseCSVFile: function (csvContent) {
      const content = parse(csvContent)

      let craftsmen = []
      for (let i = 1; i < content.length; i++) {
        let entry = content[i]

        let craftsman = {
          trade: entry[0],
          company: entry[1],
          contactName: entry[2],
          email: entry[3],
          emailCCs: []
        }

        if (entry[4]) {
          craftsman.emailCCs = entry[4].split(",").map(e => e.trim()).filter(e => e)
        }

        craftsmen.push(craftsman)
      }

      this.$emit('imported', craftsmen)
    },
  },
  computed: {
    validFileTypes: function () {
      return ['text/csv', 'text/plain']
    },
    fileIsValid: function () {
      if (!this.file) {
        return false
      }

      return this.validFileTypes.some(e => this.file.type === e)
    },
    downloadSampleCSVHref: function () {
      const blob = new Blob([this.sampleCSVString], {type: 'text/csv'})
      return window.URL.createObjectURL(blob);
    },
    sampleCSVString: function () {
      const translatedHeader = header.map(h => this.$t('craftsman.'+h))
      let content = [translatedHeader, ...defaultContent]
      if (this.craftsmen) {
        content = [translatedHeader, ...this.craftsmen.map(c => [c.trade, c.company, c.contactName, c.email, c.emailCCs.join(", ")])]
      }

      return stringify(content)
    }
  }
}
</script>
