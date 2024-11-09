<template>
  <div class="card">
    <div class="card-body limited-height">
      <div class="loading-center" v-if="protocolEntries === null">
        <loading-indicator-secondary/>
      </div>
      <template v-else>
        <add-protocol-entry-button
            :authority-iri="constructionManagerIri" :root="constructionSite"
            :construction-site="constructionSite"
            @added="protocolEntries.push($event)"/>

        <div class="mt-3" v-if="orderedProtocolEntries.length">
          <protocol-entry v-for="(entry, index) in orderedProtocolEntries" :id="entry['@id']"
                          :last="index+1 === orderedProtocolEntries.length"
                          :protocol-entry="entry"
                          :root="constructionSite"
                          :created-by="responsiblesLookup[entry['createdBy']]"
          />
        </div>

      </template>
    </div>
  </div>
</template>

<script>

import LoadingIndicatorSecondary from "./Library/View/LoadingIndicatorSecondary.vue";
import {api, iriToId} from "../services/api";
import ProtocolEntry from "./View/ProtocolEntry.vue";
import AddProtocolEntryButton from "./Action/AddProtocolEntryButton.vue";
import {sortProtocolEntries} from "../services/sorters";

export default {
  components: {
    AddProtocolEntryButton, ProtocolEntry,
    LoadingIndicatorSecondary,
  },
  data() {
    return {
      protocolEntries: null,
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: false
    },
  },
  computed: {
    responsiblesLookup: function () {
      let responsiblesLookup = {}
      this.constructionManagers.forEach(cm => responsiblesLookup[iriToId(cm['@id'])] = cm)

      return responsiblesLookup
    },
    orderedProtocolEntries: function () {
      const protocolEntries = [...this.protocolEntries]
      sortProtocolEntries(protocolEntries)
      return protocolEntries
    }
  },
  mounted() {
    api.getProtocolEntries(this.constructionSite, this.constructionSite)
        .then(entries => {
          this.protocolEntries = entries
        })
  }
}
</script>

<style scoped>
.limited-height {
  max-height: 30em;
  overflow-y: auto;
}

</style>
