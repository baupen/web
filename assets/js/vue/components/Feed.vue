<template>
  <div>
    <div v-for="entry in orderedEntries">
      {{entry}}
    </div>
  </div>
</template>

<script>

import SummaryCard from './IssuesSummary/SummaryCard'
import FeedEntry from './Feed/FeedEntry'

export default {
  components: { FeedEntry, SummaryCard },
  props: {
    entries: {
      type: Array,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    }
  },
  computed: {
    orderedEntries: function () {
      return this.entries.sort((a, b) => a.date.localeCompare(b.date))
    }
  }
}
</script>
