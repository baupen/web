<template>
  <span>
    <!--
    public const TYPE_CONSTRUCTION_MANAGER_REGISTERED = 1;
    public const TYPE_CRAFTSMAN_RESOLVED = 2;
    public const TYPE_CONSTRUCTION_MANAGER_CLOSED = 3;

    public const TYPE_CRAFTSMAN_VISITED_WEBPAGE = 10;
    -->
    <span v-if="entry.type === 1">
      {{$tc('feed.entries.construction_manager_registered', this.entry.count, {'constructionManager': this.constructionManagerName})}}
    </span>
    <span v-else-if="entry.type === 2">
      {{$tc('feed.entries.craftsman_resolved', this.entry.count, {'craftsman': this.craftsmanName})}}
    </span>
    <span v-else-if="entry.type === 3">
      {{$tc('feed.entries.construction_manager_closed', this.entry.count, {'constructionManager': this.constructionManagerName})}}
    </span>
    <span v-else-if="entry.type === 10">
      {{$tc('feed.entries.craftsman_visited_webpage', this.entry.count, {'craftsman': this.craftsmanName})}}
    </span>
    <span class="text-secondary">
      -
      <date-human-readable :value="entry.date" />
    </span>
  </span>
</template>

<script>

import DateHumanReadable from '../Library/View/DateHumanReadable'
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
      return this.craftsmen.find(c => c['@id'] === this.entry.subject).contactName;
    },
    constructionManagerName: function () {
      const constructionManager = this.constructionManagers.find(c => c['@id'] === this.entry.subject);
      return constructionManager.givenName + " " + constructionManager.familyName;
    }
  }
}
</script>
