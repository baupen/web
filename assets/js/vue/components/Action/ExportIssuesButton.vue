<template>
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

    <hr />

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

        <report-form :template="report" @update="report = $event" />

        <generate-issues-report
            :construction-site="constructionSite" :maps="maps" :report-configuration="report"
            :query="applyingQuery" :query-result-size="applyingQueryResultSize"
        />
      </div>
      <div class="tab-pane fade" :class="{'show active': exportType === 'link'}">
        <p class="alert alert-info">
          {{ $t('export_issues_button.export_type.link.help') }}
        </p>

        <filter-form :template="link" @update="link = $event" />

      </div>
    </div>
  </button-with-modal>
</template>

<script>

import { iriToId } from '../../services/api'
import ButtonWithModal from '../Library/Behaviour/ButtonWithModal'
import CustomRadioField from '../Library/FormLayout/CustomRadioField'
import FormField from '../Library/FormLayout/FormField'
import ReportForm from '../Form/ReportForm'
import FilterForm from '../Form/FilterForm'
import GenerateIssuesReport from './GenerateIssuesReport'

export default {
  components: {
    GenerateIssuesReport,
    FilterForm,
    ReportForm,
    FormField,
    CustomRadioField,
    ButtonWithModal,
  },
  emits: ['send', 'save-template', 'create-template'],
  data () {
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

      reportRequested: false
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
      required: true
    },
    maps: {
      type: Array,
      default: []
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
  watch: {
    selectedIssues: {
      deep: true,
      handler: function () {
        if (this.selectedIssues.length === 0) {
          this.exportSource = 'filter'
        }
      }
    }
  },
  computed: {
    selectedIssueNumbers: function () {
      return this.selectedIssues.map(issue => issue.number)
    },
    applyingQueryResultSize: function () {
      if (this.exportSource === 'filter') {
        return this.queriedIssueCount
      } else {
        return this.selectedIssues.length
      }
    },
    applyingQuery: function () {
      if (this.exportSource === 'filter') {
        return this.query
      } else {
        return {
          constructionSite: iriToId(this.constructionSite['@id']),
          'number[]': this.selectedIssueNumbers
        }
      }
    }
  },
  methods: {
    createReport: function () {
      this.reportRequested = true
    },
    createLink: function () {
      console.log('create link')
    },
  },
}
</script>
