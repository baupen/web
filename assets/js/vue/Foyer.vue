<template>
  <div id="foyer">
    <loading-indicator :spin="isLoading">
      <foyer-issues :construction-site="constructionSite" :construction-manager-iri="constructionManagerIri"/>
    </loading-indicator>
  </div>
</template>

<script>
import { api, apiClient } from './domain/api'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import FoyerIssues from './components/FoyerIssues'
import { meStore, store } from './domain/stores'

export default {
  components: {
    FoyerIssues,
    LoadingIndicator
  },
  data () {
    return {
      constructionManagerIri: null,
      constructionSite: null,
    }
  },
  computed: {
    isLoading: function () {
      return !this.constructionSite || !this.constructionManagerIri
    }
  },
  mounted () {
    const me = meStore.me
    this.constructionManagerIri = me.constructionManagerIri
    this.constructionSite = store.constructionSite
  }
}

</script>
