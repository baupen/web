<template>
  <div class="mb-5">
    <h1>{{ $t('resolve.issues') }}</h1>
    <p>{{ $t('resolve.issues_help') }}</p>

    <p>{{ craftsman }}</p>
  </div>
</template>

<script>
import ConstructionSitesEnterMasonry from './View/ConstructionSitesEnterMasonry'
import ConstructionSitesParticipationTable from './View/ConstructionSitesParticipationTable'
import AddConstructionSiteButton from './Action/AddConstructionSiteButton'
import LoadingIndicator from './Library/View/LoadingIndicator'
import { addNonDuplicatesById, api } from '../services/api'
import LoadingIndicatorSecondary from './Library/View/LoadingIndicatorSecondary'

export default {
  components: {
    LoadingIndicatorSecondary,
    ConstructionSitesEnterMasonry,
    ConstructionSitesParticipationTable,
    AddConstructionSiteButton,
    LoadingIndicator
  },
  data () {
    return {
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
  mounted () {
    this.constructionManagers = [this.constructionManager]
    api.getMaps(this.constructionSite)
        .then(maps => this.maps = maps)
  }
}
</script>
