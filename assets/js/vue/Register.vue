<template>
  <div id="dispatch">
    <loading-indicator :spin="isLoading">
      <register-issues :construction-site="constructionSite" :construction-manager-iri="constructionManagerIri" />
    </loading-indicator>
  </div>
</template>

<script>
import {api} from './services/api'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import RegisterIssues from './components/RegisterIssues'

export default {
  components: {
    RegisterIssues,
    LoadingIndicator
  },
  data() {
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
  mounted() {
    api.setupErrorNotifications(this.$t)
    api.getMe()
        .then(me => this.constructionManagerIri = me.constructionManagerIri)
    api.getConstructionSite()
        .then(constructionSite => {
          this.constructionSite = constructionSite
        })
  }
}

</script>
