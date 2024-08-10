<template>
  <div class="row mb-2">
    <div class="col-3">
      {{ $t('protocol_entry._plural_short') }}
    </div>
    <div class="col">
      <add-protocol-entry-button
          :authority-iri="authorityIri" :root="constructionSite"
          :construction-site="constructionSite"
          @added="newProtocolEntries.push($event)"/>
    </div>
  </div>

  <protocol-entry v-for="(entry, index) in orderedProtocolEntries" :id="entry['@id']"
                  :last="index+1 === orderedProtocolEntries.length"
                  :protocol-entry="entry"
                  :root="constructionSite"
                  :is-removable="newProtocolEntries.includes(entry)"
                  :created-by="responsiblesLookup[entry['createdBy']]"
  />
</template>

<script>

import {api, iriToId} from "../../services/api";
import ProtocolEntry from "./ProtocolEntry.vue";
import LoadingIndicatorSecondary from "../Library/View/LoadingIndicatorSecondary.vue";
import AddProtocolEntryButton from "../Action/AddProtocolEntryButton.vue";

export default {
  components: {
    AddProtocolEntryButton,
    LoadingIndicatorSecondary,
    ProtocolEntry,
  },
  data() {
    return {
      newProtocolEntries: [],
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
    protocolEntries: {
      type: Array,
      required: true
    },
    authorityIri: {
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
      const protocolEntries = [...this.newProtocolEntries, ...(this.protocolEntries ?? [])]
          .filter(entry => !entry.isDeleted)
      protocolEntries.sort((a, b) => b.createdAt.localeCompare(a.createdAt))
      return protocolEntries
    }
  },
}
</script>
