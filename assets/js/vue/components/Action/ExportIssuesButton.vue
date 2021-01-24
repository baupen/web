<template>
  <div>
    <button-with-modal
        :title="$t('register.actions.export_issues')"
        :button-disabled="disabled">

      <custom-radio-field for-id="export-source-filter"
                          :label="$tc('export_issues_button.export_source_filter', queriedIssueCount)">
        <input id="export-source-filter" class="custom-control-input" type="radio"
               name="export-source" value="filter"
               :disabled="queriedIssueCount === 0"
               v-model="exportSource">
      </custom-radio-field>

      <custom-radio-field for-id="export-source-selection"
                          :label="$tc('export_issues_button.export_source_selection', selectedIssues.length)">
        <input id="export-source-selection" class="custom-control-input" type="radio"
               name="export-source" value="selection"
               :disabled="selectedIssues.length === 0"
               v-model="exportSource">
      </custom-radio-field>

      <hr/>

      <ul class="nav nav-tabs" id="export-type-settings">
        <li class="nav-item">
          <a class="nav-link" :class="{'active': exportType === 'report'}" @click="exportType = 'report'">
            {{ $t('export_issues_button.export_type.report.name') }}
          </a>
        </li>
        <li class="nav-item" v-if="false">
          <a class="nav-link" :class="{'active': exportType === 'link'}" @click="exportType = 'link'">
            {{ $t('export_issues_button.export_type.link.name') }}
          </a>
        </li>
      </ul>
      <div class="tab-content p-3 border border-top-0">
        <div class="tab-pane fade" :class="{'show active': exportType === 'report'}">
          <p class="alert alert-info">
            {{ $t('export_issues_button.export_type.report.help') }}
          </p>

          <custom-checkbox-field
              for-id="report-with-images"
              :label="$t('export_issues_button.export_type.report.with_images')">
            <input class="custom-control-input" type="checkbox" name="report-with-images" id="report-with-images"
                   :true-value="true" :false-value="false"
                   v-model="report.withImages">
          </custom-checkbox-field>

          <p class="mb-0">{{ $t('export_issues_button.export_type.report.summary_tables') }}</p>
          <div class="form-group">
            <custom-checkbox
                class="mb-1"
                for-id="report-table-by-craftsman"
                :label="$t('export_issues_button.export_type.report.by_craftsman')">
              <input class="custom-control-input" type="checkbox" name="report-table-by-craftsman" value="selection"
                     id="report-table-by-craftsman"
                     :true-value="true" :false-value="false"
                     v-model="report.tableByCraftsman">
            </custom-checkbox>

            <custom-checkbox
                for-id="report-table-by-map"
                :label="$t('export_issues_button.export_type.report.by_map')">
              <input class="custom-control-input" type="checkbox" name="report-table-by-map" value="selection"
                     id="report-table-by-map"
                     :true-value="true" :false-value="false"
                     v-model="report.tableByMap">
            </custom-checkbox>
          </div>

          <a class="btn btn-primary" target="_blank" :href="reportLink">
            {{$t('export_issues_button.generate')}}
          </a>
        </div>
        <div class="tab-pane fade" :class="{'show active': exportType === 'link'}">
          <p class="alert alert-info">
            {{ $t('export_issues_button.export_type.link.help') }}
          </p>

          <form-field for-id="link-access-allowed-before"
                      :label="$t('export_issues_button.export_type.link.access_allowed_before')">
            <flat-pickr
                id="link-access-allowed-before" class="form-control"
                v-model="link.accessAllowedBefore"
                :config="datePickerConfig">
            </flat-pickr>
          </form-field>
        </div>
      </div>
    </button-with-modal>
  </div>
</template>

<script>

import ButtonWithModalConfirm from './Behaviour/ButtonWithModalConfirm'
import CustomCheckboxField from './Edit/Layout/CustomCheckboxField'
import ButtonWithModal from "./Behaviour/ButtonWithModal";
import FormField from "./Edit/Layout/FormField";
import InvalidFeedback from "./Edit/Layout/InvalidFeedback";
import {dateConfig, flatPickr} from "../services/flatpickr";
import CustomRadioField from "./Edit/Layout/CustomRadioField";
import CustomCheckbox from "./Edit/Input/CustomCheckbox";
import {api, iriToId} from "../services/api";

export default {
  components: {
    CustomCheckbox,
    CustomRadioField,
    InvalidFeedback,
    FormField,
    ButtonWithModal,
    CustomCheckboxField,
    ButtonWithModalConfirm,
    flatPickr
  },
  emits: ['send', 'save-template', 'create-template'],
  data() {
    return {
      exportSource: 'filter',
      exportType: 'report',
      report: {
        withImages: true,
        tableByCraftsman: true,
        tableByMap: false
      },
      link: {
        accessAllowedBefore: null
      },
    }
  },
  props: {
    selectedIssues: {
      type: Array,
      required: true
    },
    queriedIssueCount: {
      type: Number,
      required: true
    },
    constructionSite: {
      type: Object,
    },
    query: {
      type: Object,
      required: true
    },
    disabled: {
      type: Boolean,
      required: true
    }
  },
  computed: {
    datePickerConfig: function () {
      return dateConfig;
    },
    selectedIssueNumbers: function () {
      return this.selectedIssues.map(issue => issue.number)
    },
    reportQuery: function () {
      return {
        "report[withImages]": this.report.withImages,
        "report[tableByCraftsman]": this.report.tableByCraftsman,
        "report[tableByMap]": this.report.tableByMap,
      }
    },
    reportLink: function () {
      if (!this.constructionSite) {
        return null
      }

      if (this.exportSource === 'filter') {
        return api.getReportLink(this.constructionSite, this.reportQuery, this.query)
      } else {
        return api.getReportLink(this.constructionSite, this.reportQuery, {'number[]': this.selectedIssueNumbers})
      }
    }
  },
  methods: {
    createLink: function () {
      console.log('create link')
    },
  },
  watch: {
    selectedIssues: {
      deep: true,
      handler: function (){
        if (this.selectedIssues.length === 0) {
          this.exportSource = 'filter'
        }
      }
    }
  }
}
</script>
