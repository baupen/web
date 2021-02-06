<template>
  <div class="mb-5">
    <h1>{{ $t('resolve.issues') }}</h1>
    <p>{{ $t('resolve.issues_help') }}</p>

    <loading-indicator-secondary :spin="isLoading">
      <issues-resolve-masonry :craftsman="craftsman" :maps="maps" :construction-managers="constructionManagers" :issues="issues" />
    </loading-indicator-secondary>
  </div>
</template>

<script>
import ConstructionSitesEnterMasonry from './View/ConstructionSitesEnterMasonry'
import ConstructionSitesParticipationTable from './View/ConstructionSitesParticipationTable'
import AddConstructionSiteButton from './Action/AddConstructionSiteButton'
import LoadingIndicator from './Library/View/LoadingIndicator'
import { addNonDuplicatesById, api, iriToId } from '../services/api'
import LoadingIndicatorSecondary from './Library/View/LoadingIndicatorSecondary'
import IssuesResolveMasonry from './View/IssuesResolveMasonry'

export default {
  components: {
    IssuesResolveMasonry,
    LoadingIndicatorSecondary,
    ConstructionSitesEnterMasonry,
    ConstructionSitesParticipationTable,
    AddConstructionSiteButton,
    LoadingIndicator
  },
  data () {
    return {
      constructionManagers: null,
      maps: null,
      issueSummary: null,
      issues: null,
    }
  },
  props: {
    craftsmanIri: {
      type: String,
      required: true
    },
    craftsman: {
      type: Object,
      required: true
    },
    constructionSite: {
      type: Object,
      required: true
    }
  },
  computed: {
    isLoading: function () {
      return !this.constructionManagers || !this.maps || !this.issues
    },
    query: function () {
      return {craftsman: iriToId(this.craftsman['@id']), isDeleted: false, state: 2}
    }
  },
  mounted () {
    api.getConstructionManagers(this.constructionSite)
        .then(constructionManagers => {
          this.constructionManagers = constructionManagers
        })

    api.getMaps(this.constructionSite)
        .then(maps => this.maps = maps)

    api.getPaginatedIssues(this.constructionSite, this.query)
        .then(payload => {
          this.issues = payload.items
        })
  }
}
</script>
