<template>
  <div id="dispatch">
    <div class="btn-group mb-4">
      <register-issues-button
          :construction-manager-iri="constructionManagerIri" :issues="selectedIssues"
          @registered="hiddenIssues.push($event)" />
    </div>

    <issues-table
        :construction-manager-iri="constructionManagerIri"
        :construction-site="constructionSite"
        :construction-managers="constructionManagers"
        :craftsmen="craftsmen"
        :maps="maps"
        :hidden-issues="hiddenIssues"
        view="foyer"
        @reset-hidden="hiddenIssues = []"
        @selected="selectedIssues = $event" />
  </div>
</template>

<script>

import IssuesTable from './View/IssuesTable'
import RegisterIssuesButton from './Action/RegisterIssuesButton'

export default {
  components: {
    RegisterIssuesButton,
    IssuesTable,
  },
  data () {
    return {
      selectedIssues: [],
      hiddenIssues: []
    }
  },
  props: {
    constructionManagerIri: {
      type: String,
      required: true
    },
    constructionSite: {
      type: Object,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true
    },
    maps: {
      type: Array,
      required: true
    },
  },
}

</script>
