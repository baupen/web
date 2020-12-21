<template>
  <div>
    <div class="card-group shadow">
      <summary-card
        :number="summary.openCount"
        :description="$t('issue.state.open')"
      />
      <summary-card
        :number="summary.resolvedCount"
        :description="$t('issue.state.resolved')"
      />
      <summary-card
        :number="summary.closedCount"
        :description="$t('issue.state.closed')"
      />
    </div>
  </div>
</template>

<script>

import { constructionSiteFormatter } from '../services/formatters'
import SummaryCard from './IssuesSummary/SummaryCard'

export default {
  components: { SummaryCard },
  props: {
    summary: {
      type: Object,
      required: true
    }
  },
  computed: {
    address: function () {
      return constructionSiteFormatter.address(this.constructionSite)
    }
  }
}
</script>
