<template>
  <div class="card-group shadow">
    <card-counter-animated
        color="primary"
        :target="issuesSummary.openCount" :description="$t('issue.state.open')" />
    <card-counter-animated
        color="warning"
        :target="issuesSummary.inspectableCount" :description="$t('issue.state.to_inspect')" />
    <card-counter-animated
        color="success"
        :target="issuesSummary.closedCount" :description="$t('issue.state.closed')" />
  </div>
</template>

<script>
import { api } from '../services/api'
import CardCounterAnimated from './Library/View/CardCounterAnimated'

export default {
  data () {
    return {
      issuesSummary: {
        openCount: 0,
        inspectableCount: 0,
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
