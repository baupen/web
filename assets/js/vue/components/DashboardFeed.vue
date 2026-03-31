<template>
  <div class="card">
    <div class="card-body limited-height">
      <div class="loading-center" v-if="isLoading">
        <loading-indicator-secondary />
      </div>
      <feed v-else :construction-managers="constructionManagers" :craftsmen="craftsmen" :issue-events="issueEvents" />
    </div>
    <div class="card-footer" v-if="!isLoadingStatistics && (overdueCount > 0 || unreadCount > 0)">
      <span v-if="overdueCount" class="badge bg-danger me-1">
        {{ overdueCount }} {{ $t('issue.state.overdue') }}
      </span>
      <span v-if="unreadCount" class="badge bg-secondary me-1">
        {{ unreadCount }} {{ $t('issue.state.unread') }}
      </span>
      <a :href="dispatchUrl">{{ $t('dispatch.title') }}</a>
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
      craftsmenStatistics: null,
      issueEvents: null,
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true
    }
  },
  computed: {
    isLoading: function () {
      return !this.issueEvents
    },
    isLoadingStatistics: function () {
      return !this.craftsmen || !this.craftsmenStatistics
    },
    overdueCount: function () {
      return this.craftsmenStatistics.reduce((acc, curr) => acc + curr.issueOverdueCount, 0)
    },
    unreadCount: function () {
      return this.craftsmenStatistics.reduce((acc, curr) => acc + curr.issueUnreadCount, 0)
    },
    dispatchUrl: function () {
      return api.currentDispatchUrl()
    }
  },
  mounted () {
    api.getRecentIssueEvents(this.constructionSite)
        .then(issueEvents => {
          this.issueEvents = issueEvents
        })

    api.getCraftsmenStatistics(this.constructionSite, { isDeleted: false })
        .then(craftsmenStatistics => this.craftsmenStatistics = craftsmenStatistics)
  }
}
</script>


<style scoped>
.limited-height {
  max-height: 32em;
  overflow-y: auto;
}

.loading-center > * {
  display: block;
  margin: 0 auto;
}
</style>
