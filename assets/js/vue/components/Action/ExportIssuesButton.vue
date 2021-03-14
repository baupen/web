<template>
  <button-with-modal
      :title="$t('_action.export_issues.title')"
      :button-disabled="disabled">

    <custom-radio-field for-id="export-source-filter"
                        :label="$tc('_action.export_issues.export_source_filter', queriedIssueCount)">
      <input id="export-source-filter" class="custom-control-input" type="radio"
             name="export-source" value="filter"
             :disabled="queriedIssueCount === 0"
             v-model="exportSource">
    </custom-radio-field>

    <custom-radio-field for-id="export-source-selection"
                        :label="$tc('_action.export_issues.export_source_selection', selectedIssues.length)">
      <input id="export-source-selection" class="custom-control-input" type="radio"
             name="export-source" value="selection"
             :disabled="selectedIssues.length === 0"
             v-model="exportSource">
    </custom-radio-field>

    <hr />

    <ul class="nav nav-tabs" id="export-type-settings">
      <li class="nav-item">
        <a class="nav-link" :class="{'active': exportType === 'report'}" @click="exportType = 'report'">
          {{ $t('_action.export_issues_report.title') }}
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{'active': exportType === 'link'}" @click="exportType = 'link'">
          {{ $t('_action.export_issues_link.title') }}
        </a>
      </li>
    </ul>
    <div class="tab-content p-3 border border-top-0">
      <div class="tab-pane fade" :class="{'show active': exportType === 'report'}">
        <export-issues-report-view
            :construction-site="constructionSite" :maps="maps"
            :query="applyingQuery" :query-result-size="applyingQueryResultSize"
            :show-title="false"
        />
      </div>
      <div class="tab-pane fade" :class="{'show active': exportType === 'link'}">
        <export-issues-link-view
            :construction-site="constructionSite"
            :query="applyingQuery"
            :show-title="false"
        />
      </div>
    </div>
  </button-with-modal>
</template>

<script>

import { iriToId } from '../../services/api'
import ButtonWithModal from '../Library/Behaviour/ButtonWithModal'
import CustomRadioField from '../Library/FormLayout/CustomRadioField'
import FormField from '../Library/FormLayout/FormField'
import ReportForm from '../Form/IssueReportForm'
import FilterForm from '../Form/IssueLinkForm'
import GenerateIssuesReport from './GenerateIssuesReport'
import GenerateIssuesFilter from './GenerateIssuesLink'
import ExportIssuesReportView from './ExportIssuesReportView'
import ExportIssuesLinkView from './ExportIssuesLinkView'

export default {
  components: {
    ExportIssuesLinkView,
    ExportIssuesReportView,
    GenerateIssuesFilter,
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
}
</script>
