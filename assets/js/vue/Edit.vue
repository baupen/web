<template>
  <div id="edit">
    <loading-indicator :spin="loading">
      <edit-construction-site :construction-site="constructionSite" />
      <edit-maps :construction-site="constructionSite" />
      <edit-craftsmen :construction-site="constructionSite" />
      <edit-construction-managers :construction-site="constructionSite" :construction-manager="constructionManager" />
    </loading-indicator>
  </div>
</template>

<script>
import {api} from './services/api'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import EditCraftsmen from './components/EditCraftsmen'
import EditMaps from './components/EditMaps'
import EditConstructionSite from './components/EditConstructionSite'
import EditConstructionManagers from './components/EditConstructionManagers'

export default {
  components: {
    EditConstructionManagers,
    EditConstructionSite,
    EditMaps,
    EditCraftsmen,
    LoadingIndicator,
  },
  data() {
    return {
      constructionSite: null,
      constructionManagerIri: null,
      constructionManager: null
    }
  },
  computed: {
    loading: function () {
      return !this.constructionSite || !this.constructionManager
    }
  },
  mounted() {
    api.setupErrorNotifications(this.$t)
    api.getMe()
        .then(me => {
          this.constructionManagerIri = me.constructionManagerIri
          api.getById(this.constructionManagerIri).then(constructionManager => {
            this.constructionManager = constructionManager
          })
        })

    api.getConstructionSite()
        .then(constructionSite => {
          this.constructionSite = constructionSite
        })
  }
}

</script>
