<template>
  <div id="switch">
    <loading-indicator :spin="isLoading">
      <switch-construction-sites
          :construction-manager-iri="constructionManagerIri" />
    </loading-indicator>
  </div>
</template>

<script>
import { api } from './services/api'
import SwitchConstructionSites from './components/SwitchConstructionSites'
import LoadingIndicator from './components/Library/View/LoadingIndicator'

export default {
  components: {
    LoadingIndicator,
    SwitchConstructionSites,
  },
  data () {
    return {
      constructionManagerIri: null,
    }
  },
  computed: {
    isLoading: function () {
      return !this.constructionManagerIri
    },
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
    api.getMe()
        .then(me => this.constructionManagerIri = me.constructionManagerIri)
  }
}

</script>
