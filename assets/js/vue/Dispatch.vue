<template>
  <div id="dispatch">
    <loading-indicator :spin="!constructionSite || !constructionManagers">
      <dispatch-craftsmen :construction-manager-iri="constructionManagerIri" :construction-site="constructionSite" :construction-managers="constructionManagers" />
    </loading-indicator>
  </div>
</template>

<script>
import { api } from './services/api'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import DispatchCraftsmen from './components/DispatchCraftsmen'

export default {
  components: {
    DispatchCraftsmen,
    LoadingIndicator
  },
  data () {
    return {
      constructionManagerIri: null,
      constructionSite: null,
      constructionManagers: null
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

          api.getConstructionManagers(this.constructionSite)
              .then(constructionManagers => this.constructionManagers = constructionManagers)
        })
  }
}

</script>

<style scoped>
.min-width-600 {
  min-width: 600px;
}
</style>
