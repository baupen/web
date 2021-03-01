<template>
  <div class="card">
    <div class="card-body limited-height">
      <loading-indicator-secondary v-if="isLoading" class="loading-center" />
      <template v-else-if="orderedFeedEntries.length > 0" v-for="(entry, index) in orderedFeedEntries">
        <hr v-if="index !== 0" />
        <feed-entry :entry="entry" :construction-managers="constructionManagers" :craftsmen="craftsmen" />
      </template>
      <span v-else><i>{{$t("feed.no_entries")}}</i></span>
    </div>
  </div>
</template>
<script>

import FeedEntry from './View/FeedEntry'
import { api } from '../services/api'
import LoadingIndicatorSecondary from './Library/View/LoadingIndicatorSecondary'

export default {
  components: {
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
    orderedFeedEntries: function () {
      return this.feedEntries.sort((a, b) => b.date.localeCompare(a.date))
    },
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

.loading-center {
  display: block;
  margin: 0 auto;
}
</style>
