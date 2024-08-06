<template>
  <div id="dispatch">
    <loading-indicator :spin="!constructionSite">
      <dispatch-craftsmen :construction-site="constructionSite" />
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
      constructionSite: null,
    }
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
    api.authenticate()
        .then(_ => {
          api.getConstructionSite()
              .then(constructionSite => {
                this.constructionSite = constructionSite
              })
        })
  }
}

</script>

<style scoped>
.min-width-600 {
  min-width: 600px;
}
</style>
