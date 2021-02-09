<template>
  <div id="switch">
    <loading-indicator :spin="isLoading">
      <switch-construction-sites
          :construction-manager-iri="constructionManagerIri"
          :construction-manager="constructionManager" />
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
      constructionManager: null,
    }
  },
  computed: {
    isLoading: function () {
      return !this.constructionManagerIri || !this.constructionManager
    },
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
    api.authenticate()
        .then(me => {
          this.constructionManagerIri = me.constructionManagerIri
          api.getById(me.constructionManagerIri)
              .then(constructionManager => {
                this.constructionManager = constructionManager
              })
        })
  }
}

</script>
