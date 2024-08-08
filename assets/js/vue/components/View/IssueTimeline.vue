<template>
  <loading-indicator-secondary :spin="issueProtocolEntries === null || craftsmanProtocolEntries === null"/>
  <protocol-entry v-for="(entry, index) in orderedProtocolEntries" :id="entry['@id']"
                  :last="index+1 === orderedProtocolEntries.length"
                  :protocol-entry="entry"
                  :root="issue"
                  :created-by="responsiblesLookup[entry['createdBy']]"
  />
</template>

<script>

import {api, iriToId} from "../../services/api";
import ProtocolEntry from "./ProtocolEntry.vue";
import LoadingIndicatorSecondary from "../Library/View/LoadingIndicatorSecondary.vue";

export default {
  components: {
    LoadingIndicatorSecondary,
    ProtocolEntry,
  },
  data() {
    return {
      issueProtocolEntries: null,
      craftsmanProtocolEntries: null,
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    issue: {
      type: Object,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true
    },
  },
  computed: {
    responsiblesLookup: function () {
      let constructionManagerLookup = {}
      this.constructionManagers.forEach(cm => constructionManagerLookup[iriToId(cm['@id'])] = cm)
      this.craftsmen.forEach(cm => constructionManagerLookup[iriToId(cm['@id'])] = cm)

      return constructionManagerLookup
    },
    orderedProtocolEntries: function () {
      const protocolEntries = [...(this.issueProtocolEntries ?? []), ...(this.craftsmanProtocolEntries ?? [])]
      protocolEntries.sort((a, b) => b.createdAt.localeCompare(a.createdAt))
      return protocolEntries
    }
  },
  mounted() {
    api.getProtocolEntries(this.constructionSite, this.issue)
        .then(entries => {
          this.issueProtocolEntries = entries
        })

    api.getProtocolEntries(this.constructionSite, this.craftsmen.find(craftsman => craftsman['@id'] === this.issue.craftsman))
        .then(entries => {
          this.craftsmanProtocolEntries = entries
        })
  }
}
</script>
