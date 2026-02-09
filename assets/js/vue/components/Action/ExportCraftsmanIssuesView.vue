<template>
  <ul class="nav nav-tabs" id="export-type-settings">
    <li class="nav-item">
      <a class="nav-link" :class="{'active': exportType === 'report'}" @click="exportType = 'report'">
        {{ $t('_action.export_issues_report.title') }}
      </a>
    </li>
    <li class="nav-item">
      <a class="nav-link" :class="{'active': exportType === 'table'}" @click="exportType = 'table'">
        {{ $t('_action.export_issues_table.title') }}
      </a>
    </li>
  </ul>
  <div class="tab-content p-3 border border-top-0">
    <div class="tab-pane fade" :class="{'show active': exportType === 'report'}">
      <export-issues-report-view
          :construction-site="constructionSite" :maps="maps"
          :default-report-configuration="reportConfiguration"
          :query="query" :query-result-size="issuesCount"
      />
    </div>
    <div class="tab-pane fade" :class="{'show active': exportType === 'table'}">
      <export-craftsman-issues-table-view
          :construction-site="constructionSite" :map-containers="mapContainers" :construction-managers="constructionManagers"
          :query="query"
      />
    </div>
  </div>
</template>

<script>

import ButtonWithModal from '../Library/Behaviour/ButtonWithModal'
import CustomRadioField from '../Library/FormLayout/CustomRadioField'
import FormField from '../Library/FormLayout/FormField'
import ReportForm from '../Form/IssueReportForm'
import FilterForm from '../Form/IssueLinkForm'
import GenerateIssuesReport from './GenerateIssuesReport'
import GenerateIssuesFilter from './GenerateIssuesLink'
import ExportIssuesReportView from './ExportIssuesReportView'
import ExportIssuesLinkView from './ExportIssuesLinkView'
import ExportCraftsmanIssuesTableView from "./ExportCraftsmanIssuesTableView.vue";

export default {
  components: {
    ExportCraftsmanIssuesTableView,
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
  data() {
    return {
      exportType: 'report',
    }
  },
  props: {
    issuesCount: {
      type: Number,
      required: true
    },
    constructionSite: {
      type: Object,
      required: true
    },
    mapContainers: {
      type: Array,
      default: []
    },
    constructionManagers: {
      type: Array,
      default: []
    },
    query: {
      type: Object,
      required: true
    },
  },
  computed: {
    maps: function () {
      return this.mapContainers.map(c => c.entity)
    },
    reportConfiguration: function () {
      return {
        withRenders: true,
        withImages: true,
        tableByCraftsman: false,
        tableByMap: true
      }
    }
  },
}
</script>
