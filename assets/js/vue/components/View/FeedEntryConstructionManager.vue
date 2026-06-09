<template>
  <span>
    {{ this.constructionManagerName }}
    {{ $t('_view.feed.entries.start') }}
    {{ text }}.
    <span class="text-secondary">
      -
      <date-human-readable :value="date"/>
    </span>
  </span>
</template>

<script>

import DateHumanReadable from '../Library/View/DateHumanReadable'
import {constructionManagerFormatter} from '../../domain/formatters'

export default {
  components: {DateHumanReadable},
  props: {
    constructionManager: {
      type: Object,
      required: true
    },
    date: {
      type: String,
      required: true
    },
    entries: {
      type: Array,
      required: true
    },
  },
  computed: {
    textBlocks: function () {
      return this.entries.map(entry => {
        if (entry.type === 'EMAIL_COUNT') {
          return this.$t('_view.feed.entries.has_sent_mails', {count: entry.count})
        } else if (entry.type === 'STATUS_SET_COUNT') {
          switch (entry.payload) {
            case 'CREATED':
              return this.$t('_view.feed.entries.has_created', {count: entry.count})
            case 'REGISTERED':
              return this.$t('_view.feed.entries.has_registered', {count: entry.count})
            case 'CLOSED':
              return this.$t('_view.feed.entries.has_closed', {count: entry.count})
            default:
              return null
          }
        }

        return null
      }).filter(t => t !== null)
    },
    text: function () {
      let parts = [...this.textBlocks]
      if (parts.length > 1) {
        let lastEntry = parts.pop();
        let secondLastEntry = parts.pop()
        parts.push(`${secondLastEntry} ${this.$t('_view.feed.entries.glue_end')} ${lastEntry}`)
      }

      return parts.join(', ')
    },
    constructionManagerName: function () {
      return constructionManagerFormatter.name(this.constructionManager);
    }
  }
}
</script>
