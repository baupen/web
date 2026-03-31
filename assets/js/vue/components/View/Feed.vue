<template>
  <template v-if="relevantGroupedEvents.length > 0" v-for="(entry, index) in relevantGroupedEvents">
    <hr v-if="index !== 0"/>
    <feed-entry-construction-manager
        v-if="entry.constructionManager" :construction-manager="entry.constructionManager" :date="entry.date" :entries="entry.events" />

  </template>
  <span v-else><i>{{ $t('_view.feed.no_entries') }}</i></span>
</template>
<script>

import FeedEntry from './FeedEntry'
import DateHumanReadable from "../Library/View/DateHumanReadable.vue";
import {iriToId} from "../../services/api";
import FeedEntryConstructionManager from "./FeedEntryConstructionManager.vue";

export default {
  components: {
    FeedEntryConstructionManager,
    DateHumanReadable,
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
    issueEvents: {
      type: Array,
      required: true
    }
  },
  computed: {
    groupedIssueEvents: function () {
      const groups = {}
      this.issueEvents.forEach(event => {
        if (!event.lastChangedBy || !event.lastChangedAt) {
          return
        }

        const day = new Date(event.lastChangedAt).toISOString().substring(0, 10)
        const key = `${day}__${event.lastChangedBy}`

        if (!groups[key]) {
          groups[key] = {
            lastChangedBy: event.lastChangedBy,
            date: day,
            events: []
          }
        }

        groups[key].events.push(event)
      })

      return Object.values(groups)
    },
    relevantGroupedEvents: function () {
      const events = this.groupedIssueEvents.map(group => {
        const craftsman = this.craftsmen.find(c => iriToId(c['@id']) === group.lastChangedBy)
        const constructionManager = this.constructionManagers.find(c => iriToId(c['@id']) === group.lastChangedBy)
        if (craftsman) {
          // relevant: STATUS_SET (collapsed), IMAGE, TEXT, FILE
          const events = group.events.filter(e => e.type === 'IMAGE' || e.type === 'TEXT' || e.type === 'FILE')
          const statusSetCount = group.events.filter(e => e.type === 'STATUS_SET' && e.payload === 'RESOLVED').length
          if (statusSetCount > 0) {
            events.push({type: 'STATUS_SET_COUNT', payload: 'RESOLVED', count: statusSetCount})
          }

          return {
            date: group.date,
            craftsman,
            events
          }
        }
        if (constructionManager) {
          const events = [];
          ['CREATED', 'REGISTERED', 'CLOSED'].forEach(status => {
            const statusSetCount = group.events.filter(e => e.type === 'STATUS_SET' && e.payload === status).length
            if (statusSetCount > 0) {
              events.push({type: 'STATUS_SET_COUNT', payload: status, count: statusSetCount})
            }
          })

          const emailCount = group.events.filter(e => e.type === 'EMAIL').length
          if (emailCount > 0) {
            events.push({type: 'EMAIL_COUNT', count: emailCount})
          }
          return {
            date: group.date,
            constructionManager,
            events
          }
        }

        return null
      }).filter(e => e !== null && e.events.length > 0)

      this.craftsmen.forEach(craftsman => {
        if (!craftsman.lastVisitOnline) {
          return
        }

        const lastVisitOnlineDay = new Date(craftsman.lastVisitOnline).toISOString().substring(0, 10)
        const existingEvent = events.find(e => e.date === lastVisitOnlineDay && e.lastChangedBy === iriToId(craftsman['@id']))
        if (!existingEvent) {
          events.push({
            date: lastVisitOnlineDay,
            craftsman,
            events: [{type: 'CRAFTSMAN_VISITED_WEBPAGE'}]
          })
        }
      })

      return events.sort((a, b) => new Date(b.date) - new Date(a.date))
    }
  },
}
</script>
