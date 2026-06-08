<template>
  <div id="foyer">
    <loading-indicator :spin="isLoading">
      <foyer-issues :construction-site="constructionSite" :construction-manager-iri="constructionManagerIri" />
    </loading-indicator>
  </div>
</template>

<script>
import { api } from './domain/api'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import FoyerIssues from './components/FoyerIssues'

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
    api.authenticate()
        .then(me => {
          this.constructionManagerIri = me.constructionManagerIri
          api.getConstructionSite()
              .then(constructionSite => {
                this.constructionSite = constructionSite
              })
        })
  }
}

</script>
