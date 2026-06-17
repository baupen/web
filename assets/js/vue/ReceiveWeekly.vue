<template>
  <div id="receive-weekly">
    <toggle-receive-weekly v-if="constructionManager" :construction-manager="constructionManager"/>
  </div>
</template>

<script>
import ToggleReceiveWeekly from './components/Action/ToggleReceiveWeekly'
import { meStore, switchStore } from './domain/stores'

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
    const me = meStore.me
    this.constructionManager = switchStore.constructionManagers.find(constructionManager => constructionManager['@id'] === me.constructionManagerIri)
  }
}
</script>
