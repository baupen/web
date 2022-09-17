<template>
  <div id="register">
    <loading-indicator :spin="isLoading">
      <register-issues
          :construction-site="constructionSite" :construction-manager-iri="constructionManagerIri"
          :initial-state-query="initialStateQuery"
      />
    </loading-indicator>
  </div>
</template>

<script>
import { api } from './services/api'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import RegisterIssues from './components/RegisterIssues'

export default {
  components: {
    RegisterIssues,
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
    },
    initialStateQuery: function () {
      const queryString = window.location.search;
      const urlParams = new URLSearchParams(queryString);

      return urlParams.has("state") ? Number(urlParams.get("state")) : null
    }
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
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
