<template>
  <span>
    <span ref="value1" data-toggle="tooltip" :title="$t('issue.state.open')">
      {{ statistics.issueOpenCount }}
    </span> /
    <span ref="value2" data-toggle="tooltip" :title="$t('issue.state.unread')">
      {{ statistics.issueUnreadCount }}
    </span> /
    <span ref="value3" data-toggle="tooltip" :title="$t('issue.state.overdue')">
      {{ statistics.issueOverdueCount }}
    </span> /
    <span ref="value4" data-toggle="tooltip" :title="$t('issue.state.closed')">
      {{ statistics.issueClosedCount }}
    </span>
  </span>
</template>

<script>

export default {
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
