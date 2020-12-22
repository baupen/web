<template>
  <span>
    <number-with-tooltip :value="statistics.openIssueCount" :tooltip-title="$t('issue.state.open')" /> /
    <number-with-tooltip :value="statistics.issueUnreadCount" :tooltip-title="$t('issue.state.unread')" /> /
    <number-with-tooltip :value="statistics.issueOverdueCount" :tooltip-title="$t('issue.state.overdue')" /> /
    <number-with-tooltip :value="statistics.issueClosedCount" :tooltip-title="$t('issue.state.closed')" />
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
  },
  mounted() {
    $(this.$refs.value1).tooltip();
    $(this.$refs.value2).tooltip();
    $(this.$refs.value3).tooltip();
    $(this.$refs.value4).tooltip();
  }
}
</script>
