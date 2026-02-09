<template>
  <h3>{{ $t('_action.export_issues_table.title') }}</h3>
  <p class="alert alert-info">
    {{ $t('_action.export_issues_table.help') }}
  </p>

  <button class="btn btn-primary" @click="downloadExcel" :disabled="loading">
    {{ $t('_action.export_issues_table.download') }}
  </button>
</template>

<script>

import IssueLinkForm from '../Form/IssueLinkForm'
import GenerateIssuesFilter from './GenerateIssuesLink'
import {utils, writeFileXLSX} from "xlsx";
import moment from 'moment'
import {api, iriToId} from "../../services/api";
import {constructionManagerFormatter} from "../../services/formatters";


export default {
  components: {
    IssueLinkForm,
    GenerateIssuesFilter
  },
  data() {
    return {
      loading: false
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    mapContainers: {
      type: Array,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    },
    query: {
      type: Object,
      required: true
    }
  },
  methods: {
    sanitizeFilename: function (filename) {
      return filename
          .replace(/[^a-zA-Z0-9]+/g, '-')  // replace invalid chars by dash
          .replace(/-+/g, '-')         // collapse multiple dashes
          .replace(/^-|-$/g, '')       // trim leading/trailing dashes
    },
    flattenIssue: function (issue) {
      const mapContainer = this.mapContainers.find(mapContainer => mapContainer.entity['@id'] === issue.map)
      const mapName = mapContainer.mapParentNames.concat([mapContainer.entity.name]).join(" > ")
      const constructionManager = this.constructionManagers.find(constructionManager => constructionManager['@id'] === issue.createdBy)
      const constructionManagerName = constructionManagerFormatter.name(constructionManager)

      return {
        number: issue.number,
        map: mapName,
        description: issue.description,
        deadline: issue.deadline ? new Date(issue.deadline) : null,
        createdByName: constructionManagerName,
        createdByEmail: constructionManager.email,
        createdAt: issue.createdAt ? new Date(issue.createdAt) : null,
        mapRenderUrl: issue.mapRenderUrl ? new URL(issue.mapRenderUrl + "?size=full", window.location.origin).href : null,
        imageUrl: issue.imageUrl ? new URL(issue.imageUrl + "?size=full", window.location.origin).href : null,
      }
    },
    downloadExcel: async function () {
      this.loading = true;

      const issues = await api.getIssues(this.constructionSite, this.query)
      const flatIssues = issues.map(issue => this.flattenIssue(issue))
          .sort((a, b) => a.number - b.number)
      const header = [
        this.$t('issue.number'),
        this.$t('issue.map'),
        this.$t('issue.description'),
        this.$t('issue.deadline'),
        this.$t('issue.created_by'),
        this.$t('issue.created_at'),
        this.$t('issue.position'),
        this.$t('issue.image'),
      ]

      const worksheet = utils.sheet_new()
      worksheet["!cols"] = [{wch: 8}, {wch: 40}, {wch: 60}, {wch: 12}, {wch: 20}, {wch: 20}, {wch: 10}, {wch: 10}]; // set width

      // write header row (bold)
      utils.sheet_add_aoa(worksheet, [header], { origin: "A1" })

      // write flatIssues rows
      const rows = flatIssues.map((fi) => {
        const createdByCell = { v: fi.createdByName, t: "s", l: { Target: `mailto:${fi.createdByEmail}` } }

        const deadlineCell = fi.deadline
            ? { v: fi.deadline, t: "d", z: "yyyy-mm-dd" }
            : { v: "", t: "s" }

        const createdAtCell = fi.createdAt
            ? { v: fi.createdAt, t: "d", z: "yyyy-mm-dd hh:mm" }
            : { v: "", t: "s" }

        const mapRenderCell = fi.mapRenderUrl
            ? { v: this.$t('_action.export_issues_table.view'), t: "s", l: { Target: fi.mapRenderUrl } }
            : { v: "", t: "s" }

        const imageCell = fi.imageUrl
            ? { v: this.$t('_action.export_issues_table.view'), t: "s", l: { Target: fi.imageUrl } }
            : { v: "", t: "s" }

        return [
          fi.number,
          fi.map,
          fi.description,
          deadlineCell,
          createdAtCell,
          createdByCell,
          mapRenderCell,
          imageCell,
        ]
      })

      utils.sheet_add_aoa(worksheet, rows, { origin: "A2" })

      const name = this.$t('_action.export_issues_table.filename');
      const workbook = utils.book_new();
      utils.book_append_sheet(workbook, worksheet, name);

      const datePrefix = moment().format('DD-MM-YYYY-HHmm')
      const constructionSiteName = this.sanitizeFilename(this.constructionSite.name);
      writeFileXLSX(workbook, datePrefix + "_" + constructionSiteName + "_" + name + ".xlsx", {compression: true});
      this.loading = false;
    }
  }
}
</script>
