<template>
  <div class="form-group">
    <custom-checkbox
        class="mb-1"
        for-id="report-table-by-craftsman"
        :label="$t('_form.issue_report.table_by_craftsman')">
      <input class="custom-control-input" type="checkbox" name="report-table-by-craftsman" value="selection"
             id="report-table-by-craftsman"
             :true-value="true" :false-value="false"
             v-model="report.tableByCraftsman">
    </custom-checkbox>
    <custom-checkbox
        class="mb-1"
        for-id="report-table-by-map"
        :label="$t('_form.issue_report.table_by_map')">
      <input class="custom-control-input" type="checkbox" name="report-table-by-map" value="selection"
             id="report-table-by-map"
             :true-value="true" :false-value="false"
             v-model="report.tableByMap">
    </custom-checkbox>
    <custom-checkbox
        class="mb-1"
        for-id="report-with-renders"
        :label="$t('_form.issue_report.with_renders')">
      <input class="custom-control-input" type="checkbox" name="report-with-images" id="report-with-renders"
             :true-value="true" :false-value="false"
             v-model="report.withRenders">
    </custom-checkbox>
    <custom-checkbox
        for-id="report-with-images"
        :label="$t('_form.issue_report.with_images')">
      <input class="custom-control-input" type="checkbox" name="report-with-images" id="report-with-images"
             :true-value="true" :false-value="false"
             v-model="report.withImages">
    </custom-checkbox>
  </div>

  <form-field :label="$t('_form.issue_report.craftsmen')" :required="false" v-if="showMultipleCraftsmanOptions">
    <custom-checkbox
        class="mb-1"
        for-id="report-group-issues-by-craftsman"
        :label="$t('_form.issue_report.group_issues_by_craftsman')">
      <input class="custom-control-input" type="checkbox" name="report-group-issues-by-craftsman" value="selection"
             id="report-group-issues-by-craftsman"
             :disabled="report.separateReportByCraftsman"
             :true-value="true" :false-value="false"
             v-model="report.groupIssuesByCraftsman">
    </custom-checkbox>
    <custom-checkbox
        for-id="report-separate-report-by-craftsman"
        :label="$t('_form.issue_report.separate_report_by_craftsman')">
      <input class="custom-control-input" type="checkbox" name="report-separate-report-by-craftsman" value="selection"
             id="report-separate-report-by-craftsman"
             :true-value="true" :false-value="false"
             v-model="report.separateReportByCraftsman">
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
        withRenders: null,
        withImages: null,
        tableByCraftsman: null,
        tableByMap: null,
        groupIssuesByCraftsman: null,
        separateReportByCraftsman: null
      }
    }
  },
  props: {
    template: {
      type: Object
    },
    showMultipleCraftsmanOptions: {
      type: Boolean,
      default: false
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
  }
}
</script>
