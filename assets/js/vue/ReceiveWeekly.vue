<template>
  <div id="receive-weekly">
    <toggle-receive-weekly v-if="constructionManager" :construction-manager="constructionManager" />
  </div>
</template>

<script>
import { api } from './services/api'
import ToggleReceiveWeekly from './components/Action/ToggleReceiveWeekly'

export default {
  components: {
    ToggleReceiveWeekly
  },
  data () {
    return {
      constructionManager: null,
    }
  },
  mounted () {
    api.setupErrorNotifications(this.$t)
    api.authenticate()
        .then(me => {
          let constructionManagerIri = me.constructionManagerIri
          api.getById(constructionManagerIri)
              .then(constructionManager => {
                this.constructionManager = constructionManager
              })
        })
  }
}
</script>
