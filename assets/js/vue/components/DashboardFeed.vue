<template>
  <div class="card">
    <div class="card-body limited-height">
      <div class="loading-center" v-if="isLoading">
        <loading-indicator-secondary />
      </div>
      <feed v-else :construction-managers="constructionManagers" :craftsmen="craftsmen" :feed-entries="feedEntries" />
    </div>
  </div>
</template>
<script>

import FeedEntry from './View/FeedEntry'
import { api } from '../services/api'
import LoadingIndicatorSecondary from './Library/View/LoadingIndicatorSecondary'
import Feed from './View/Feed'

export default {
  components: {
    Feed,
    LoadingIndicatorSecondary,
    FeedEntry
  },
  data () {
    return {
      constructionManagers: null,
      craftsmen: null,
      feedEntries: null
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  computed: {
    isLoading: function () {
      return !this.feedEntries || !this.constructionManagers || !this.craftsmen
    },
  },
  mounted () {
    api.getConstructionManagers(this.constructionSite)
        .then(constructionManagers => this.constructionManagers = constructionManagers)

    api.getCraftsmen(this.constructionSite)
        .then(craftsmen => this.craftsmen = craftsmen)

    api.getIssuesFeedEntries(this.constructionSite)
        .then(issuesFeedEntries => {
          api.getCraftsmenFeedEntries(this.constructionSite)
              .then(craftsmenFeedEntries => {
                this.feedEntries = craftsmenFeedEntries.concat(issuesFeedEntries)
              })
        })
  }
}
</script>


<style scoped="true">
.limited-height {
  max-height: 32em;
  overflow-y: auto;
}

.loading-center > * {
  display: block;
  margin: 0 auto;
}
</style>
