<template>
  <div id="edit">
    <loading-indicator :spin="!constructionSite">
      <edit-craftsman :construction-site="constructionSite" />
    </loading-indicator>
  </div>
</template>

<script>
import {api} from './services/api'
import EditCraftsman from './components/EditCraftsman'
import LoadingIndicator from './components/Library/View/LoadingIndicator'

export default {
  components: {
    LoadingIndicator,
    EditCraftsman,
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
