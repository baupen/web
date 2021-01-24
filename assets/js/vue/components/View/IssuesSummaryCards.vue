<template>
  <div class="card-group shadow">
    <small-card :title="this.issuesSummary.openCount.toString()" :description="$t('issue.state.open')" />
    <small-card :title="this.issuesSummary.resolvedCount.toString()" :description="$t('issue.state.resolved')" />
    <small-card :title="this.issuesSummary.closedCount.toString()" :description="$t('issue.state.closed')" />
  </div>
</template>

<script>

import SmallCard from "./View/CardSmall";
export default {
  components: {SmallCard},
  props: {
    issuesSummary: {
      type: Object,
      required: true
    }
  },
}
</script>
