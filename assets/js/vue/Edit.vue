<template>
  <div id="edit">
    <loading-indicator :spin="!constructionSite">
      <edit-construction-site :construction-site="constructionSite" />
      <edit-maps :construction-site="constructionSite" />
      <edit-craftsmen :construction-site="constructionSite" />
    </loading-indicator>
  </div>
</template>

<script>
import {api} from './services/api'
import LoadingIndicator from './components/Library/View/LoadingIndicator'
import EditCraftsmen from './components/EditCraftsmen'
import EditMaps from './components/EditMaps'
import EditConstructionSite from './components/EditConstructionSite'

export default {
  components: {
    EditConstructionSite,
    EditMaps,
    EditCraftsmen,
    LoadingIndicator,
  },
  data() {
    return {
      constructionSite: null,
    }
  },
  mounted() {
    api.setupErrorNotifications(this.$t)
    api.getConstructionSite()
        .then(constructionSite => {
          this.constructionSite = constructionSite
        })
  }
}

</script>
