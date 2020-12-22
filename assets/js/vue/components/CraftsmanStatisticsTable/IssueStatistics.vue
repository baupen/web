<template>
  <span>
    <number-with-tooltip color-if-nonzero="secondary" :value="statistics.issueOpenCount" :tooltip-title="$t('issue.state.open')" /> /
    <number-with-tooltip color-if-nonzero="warning" :value="statistics.issueUnreadCount" :tooltip-title="$t('issue.state.unread')" /> /
    <number-with-tooltip color-if-nonzero="danger" :value="statistics.issueOverdueCount" :tooltip-title="$t('issue.state.overdue')" /> /
    <number-with-tooltip color-if-nonzero="success" :value="statistics.issueClosedCount" :tooltip-title="$t('issue.state.closed')" />
  </span>
</template>

<script>

import NumberWithTooltip from "./NumberWithTooltip";
export default {
  components: {NumberWithTooltip},
  props: {
    statistics: {
      type: Object,
      required: true
    }
  }
}
</script>
