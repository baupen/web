<template>
  <a :href="downloadXlsxExport" :download="filename">
    {{ this.$t('_action.export_craftsmen.title') }}
  </a>
</template>

<script>
import { craftsmanTransformer } from '../../services/transformers'

export default {
  props: {
    craftsmen: {
      type: Array,
      required: true
    }
  },
  computed: {
    downloadXlsxExport: function () {
      const blob = craftsmanTransformer.exportToXlsx(this.craftsmen, this.$t)
      console.log(blob)
      return window.URL.createObjectURL(blob)
    },
    filename: function () {
      const prefix = (new Date()).toISOString().slice(0, 10)
      const filename = this.$t('_action.export_craftsmen.file_name')

      return prefix + " - " + filename + ".xlsx"
    }
  }
}
</script>
