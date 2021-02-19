<template>
  <tooltip-badge color-if-nonzero="primary" :value="summary.openCount"
                  :tooltip-title="$t('issue.state.open')" />
  /
  <tooltip-badge color-if-nonzero="warning" :value="summary.resolvedCount"
                  :tooltip-title="$t('issue.state.resolved')" />
  /
  <tooltip-badge color-if-nonzero="success" :value="summary.closedCount"
                  :tooltip-title="$t('issue.state.closed')" />
</template>

<script>
import TooltipBadge from '../Library/View/TooltipBadge'

export default {
  components: { TooltipBadge },
  props: {
    summary: {
      type: Object,
      required: true
    }
  }
}
</script>
