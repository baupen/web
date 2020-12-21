<template>
  <div class="card">
    <div class="card-body limited-height">
      <div v-for="(entry, index) in orderedEntries">
        <hr v-if="index !== 0" />
        <feed-entry :entry="entry" :construction-managers="constructionManagers" :craftsmen="craftsmen" />
      </div>
    </div>
  </div>
</template>

<script>

import SummaryCard from './IssuesSummary/SummaryCard'
import FeedEntry from './Feed/FeedEntry'

export default {
  components: {
    FeedEntry,
    SummaryCard
  },
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
      return this.entries.concat(this.entries)
        .concat(this.entries)
        .concat(this.entries)
      return this.entries.sort((a, b) => b.date.localeCompare(a.date))
    }
  }
}
</script>


<style scoped="true">
.limited-height {
  max-height: 22em;
  overflow-y: auto;
}
</style>
