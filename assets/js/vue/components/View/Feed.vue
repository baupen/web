<template>
  <template v-else-if="orderedFeedEntries.length > 0" v-for="(entry, index) in orderedFeedEntries">
    <hr v-if="index !== 0" />
    <feed-entry :entry="entry" :construction-managers="constructionManagers" :craftsmen="craftsmen" />
  </template>
  <span v-else><i>{{ $t('feed.no_entries') }}</i></span>
</template>
<script>

import FeedEntry from './FeedEntry'

export default {
  components: {
    FeedEntry
  },
  props: {
    constructionManagers: {
      type: Array,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true
    },
    feedEntries: {
      type: Array,
      required: true
    }
  },
  computed: {
    orderedFeedEntries: function () {
      return this.feedEntries.sort((a, b) => b.date.localeCompare(a.date))
    },
  },
}
</script>
