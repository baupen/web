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
        :hidden-issues="hiddenIssues"
        view="foyer"
        @selected="selectedIssues = $event" />
  </div>
</template>

<script>

import LoadingIndicator from './Library/View/LoadingIndicator'
import IssuesTable from './View/IssuesTable'
import { api } from '../services/api'
import { displaySuccess } from '../services/notifiers'
import RegisterIssuesButton from './Action/RegisterIssuesButton'

export default {
  components: {
    RegisterIssuesButton,
    IssuesTable,
    LoadingIndicator
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
    }
  },
}

</script>
