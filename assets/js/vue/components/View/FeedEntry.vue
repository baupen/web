<template>
  <span>
    <!--
    public const TYPE_CONSTRUCTION_MANAGER_REGISTERED = 1;
    public const TYPE_CRAFTSMAN_RESOLVED = 2;
    public const TYPE_CONSTRUCTION_MANAGER_CLOSED = 3;

    public const TYPE_CRAFTSMAN_VISITED_WEBPAGE = 10;
    -->
    <span v-if="entry.type === 1 && this.constructionManagerName">
      {{this.constructionManagerName}}
      {{$tc('feed.entries.has_registered', this.entry.count)}}
    </span>
    <span v-else-if="entry.type === 2 && this.craftsmanName">
      <b>{{this.craftsmanName}}</b>
      {{$tc('feed.entries.has_resolved', this.entry.count)}}
    </span>
    <span v-else-if="entry.type === 3 && this.constructionManagerName">
      {{this.constructionManagerName}}
      {{$tc('feed.entries.has_closed', this.entry.count)}}
    </span>
    <span v-else-if="entry.type === 10 && this.craftsmanName">
      <b>{{this.craftsmanName}}</b>
      {{$tc('feed.entries.has_visited_webpage', this.entry.count)}}
    </span>
    <span class="text-secondary">
      -
      <date-human-readable :value="entry.date" />
    </span>
  </span>
</template>

<script>

import DateHumanReadable from '../Library/View/DateHumanReadable'
import { constructionManagerFormatter } from '../../services/formatters'
export default {
  components: { DateHumanReadable },
  props: {
    entry: {
      type: Object,
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
    craftsmanName: function () {
      return this.craftsmen.find(c => c['@id'] === this.entry.subject).company;
    },
    constructionManagerName: function () {
      const constructionManager = this.constructionManagers.find(c => c['@id'] === this.entry.subject);
      if (!constructionManager) {
        return null;
      }

      return constructionManagerFormatter.name(constructionManager);
    }
  }
}
</script>
