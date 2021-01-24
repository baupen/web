<template>
  <div class="card-group shadow">
    <card-counter-animated :target="issuesSummary.openCount" :description="$t('issue.state.open')" />
    <card-counter-animated :target="issuesSummary.resolvedCount" :description="$t('issue.state.resolved')" />
    <card-counter-animated :target="issuesSummary.closedCount" :description="$t('issue.state.closed')" />
  </div>
</template>

<script>
import { api } from '../services/api'
import CardCounterAnimated from './Library/View/CardCounterAnimated'

export default {
  data() {
    return {
      issuesSummary: {
        openCount: 0,
        resolvedCount: 0,
        closedCount: 0,
      }
    }
  },
  components: {
    CardCounterAnimated
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    }
  },
  mounted () {
    api.getIssuesSummary(this.constructionSite)
        .then(issuesSummary => this.issuesSummary = issuesSummary)
  }
}
</script>
