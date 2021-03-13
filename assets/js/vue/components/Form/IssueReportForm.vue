<template>
  <custom-checkbox-field
      for-id="report-with-images"
      :label="$t('_form.issue_report.with_images')">
    <input class="custom-control-input" type="checkbox" name="report-with-images" id="report-with-images"
           :true-value="true" :false-value="false"
           v-model="report.withImages">
  </custom-checkbox-field>

  <form-field :label="$t('_form.issue_report.summary_tables')" :required="false">
    <custom-checkbox
        class="mb-1"
        for-id="report-table-by-craftsman"
        :label="$t('_form.issue_report.by_craftsman')">
      <input class="custom-control-input" type="checkbox" name="report-table-by-craftsman" value="selection"
             id="report-table-by-craftsman"
             :true-value="true" :false-value="false"
             v-model="report.tableByCraftsman">
    </custom-checkbox>

    <custom-checkbox
        for-id="report-table-by-map"
        :label="$t('_form.issue_report.by_map')">
      <input class="custom-control-input" type="checkbox" name="report-table-by-map" value="selection"
             id="report-table-by-map"
             :true-value="true" :false-value="false"
             v-model="report.tableByMap">
    </custom-checkbox>
  </form-field>

</template>

<script>

import FormField from '../Library/FormLayout/FormField'
import InvalidFeedback from '../Library/FormLayout/InvalidFeedback'
import CustomCheckboxField from '../Library/FormLayout/CustomCheckboxField'
import CustomCheckbox from '../Library/FormInput/CustomCheckbox'

export default {
  components: {
    CustomCheckbox,
    CustomCheckboxField,
    InvalidFeedback,
    FormField
  },
  emits: ['update'],
  data () {
    return {
      report: {
        withImages: null,
        tableByCraftsman: null,
        tableByMap: null
      }
    }
  },
  props: {
    template: {
      type: Object
    }
  },
  watch: {
    report: {
      deep: true,
      handler: function () {
        this.$emit('update', this.report)
      }
    }
  },
  mounted () {
    this.report = Object.assign({}, this.template)
    this.$emit('update', this.report)
  }
}
</script>
