<template>
  <font-awesome-icon v-if="issue.closedBy" :icon="['far', 'check-circle']" />
  <font-awesome-icon v-else-if="issue.resolvedBy" :icon="['far', 'exclamation-circle']" />
  <font-awesome-icon v-else-if="issue.registeredBy" :icon="['far', 'dot-circle']" />
  <font-awesome-icon v-else :icon="['far', 'plus-circle']" />
</template>

<script>
import TooltipBadge from '../Library/View/TooltipBadge'

export default {
  components: { TooltipBadge },
  props: {
    issue: {
      type: Object,
      required: true
    }
  },
}
</script>
