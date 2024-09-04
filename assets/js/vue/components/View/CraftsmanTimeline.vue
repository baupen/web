<template>
  <loading-indicator-secondary :spin="craftsmanProtocolEntries === null">
    <div class="row mb-2">
      <div class="col-3">
        {{ $t('protocol_entry._plural_short') }}
      </div>
      <div class="col">
        <add-protocol-entry-button :authority-iri="authorityIri" :root="craftsman" :construction-site="constructionSite"
                                   @added="newProtocolEntries.push($event)"/>
      </div>
    </div>

    <protocol-entry v-for="(entry, index) in orderedProtocolEntries" :id="entry['@id']"
                    :last="index+1 === orderedProtocolEntries.length"
                    :protocol-entry="entry"
                    :root="getRoot(entry)"
                    :is-context="!(craftsmanProtocolEntries.includes(entry) || newProtocolEntries.includes(entry))"
                    :is-removable="newProtocolEntries.includes(entry)"
                    :created-by="responsiblesLookup[entry['createdBy']]"
    />
  </loading-indicator-secondary>
</template>

<script>

import {api, iriToId} from "../../services/api";
import ProtocolEntry from "./ProtocolEntry.vue";
import LoadingIndicatorSecondary from "../Library/View/LoadingIndicatorSecondary.vue";
import AddProtocolEntryButton from "../Action/AddProtocolEntryButton.vue";
import {sortProtocolEntries} from "../../services/sorters";

export default {
  components: {
    AddProtocolEntryButton,
    LoadingIndicatorSecondary,
    ProtocolEntry,
  },
  data() {
    return {
      newProtocolEntries: [],
      craftsmanProtocolEntries: null,
      constructionSiteProtocolEntries: null,
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
    craftsman: {
      type: Object,
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
      const protocolEntries = [...this.newProtocolEntries, ...(this.craftsmanProtocolEntries ?? []), ...(this.constructionSiteProtocolEntries ?? [])]
          .filter(entry => !entry.isDeleted)
      sortProtocolEntries(protocolEntries)
      return protocolEntries
    }
  },
  methods: {
    getRoot: function (protocolEntry) {
      if (this.constructionSiteProtocolEntries?.includes(protocolEntry)) {
        return this.constructionSite
      }

      return this.craftsman
    },
  },
  mounted() {
    api.getProtocolEntries(this.constructionSite, this.craftsman)
        .then(entries => {
          this.craftsmanProtocolEntries = entries
        })
    api.getProtocolEntries(this.constructionSite, this.constructionSite)
        .then(entries => {
          this.constructionSiteProtocolEntries = entries
        })
  }
}
</script>
