<template>
  <loading-indicator-secondary :spin="issueProtocolEntries === null">
    <div class="row">
      <div class="col-3">
        {{ $t('protocol_entry._plural_short') }}
      </div>
      <div class="col">
        <add-protocol-entry-button :authority-iri="authorityIri" :root="issue" :construction-site="constructionSite"
                                   @added="newProtocolEntries.push($event)"/>
      </div>
    </div>

    <protocol-entry v-for="(entry, index) in orderedProtocolEntries" :id="entry['@id']"
                    :last="index+1 === orderedProtocolEntries.length"
                    :protocol-entry="entry"
                    :root="getRoot(entry)"
                    :is-context="!(issueProtocolEntries.includes(entry) || newProtocolEntries.includes(entry))"
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
import CustomCheckboxField from "../Library/FormLayout/CustomCheckboxField.vue";
import CustomCheckbox from "../Library/FormInput/CustomCheckbox.vue";

export default {
  components: {
    CustomCheckbox,
    CustomCheckboxField,
    AddProtocolEntryButton,
    LoadingIndicatorSecondary,
    ProtocolEntry,
  },
  data() {
    return {
      newProtocolEntries: [],
      issueProtocolEntries: null,
      craftsmanProtocolEntries: null,
      constructionSiteProtocolEntries: null,
      showSecondaryEntries: false
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
    authorityIri: {
      type: String,
      required: false
    },
  },
  computed: {
    responsiblesLookup: function () {
      let responsiblesLookup = {}
      this.constructionManagers.forEach(cm => responsiblesLookup[iriToId(cm['@id'])] = cm)
      this.craftsmen.forEach(cm => responsiblesLookup[iriToId(cm['@id'])] = cm)

      return responsiblesLookup
    },
    orderedProtocolEntries: function () {
      const protocolEntries = [...this.newProtocolEntries, ...(this.issueProtocolEntries ?? []), ...(this.craftsmanProtocolEntries ?? []), ...(this.constructionSiteProtocolEntries ?? [])]
          .filter(entry => !entry.isDeleted)
      protocolEntries.sort((a, b) => b.createdAt.localeCompare(a.createdAt))
      return protocolEntries
    },
    craftsman: function () {
      return this.craftsmen.find(craftsman => craftsman['@id'] === this.issue.craftsman);
    }
  },
  methods: {
    getRoot: function (protocolEntry) {
      if (this.craftsmanProtocolEntries?.includes(protocolEntry)) {
        return this.craftsman
      }
      if (this.constructionSiteProtocolEntries?.includes(protocolEntry)) {
        return this.constructionSite
      }

      return this.issue
    },
  },
  mounted() {
    api.getProtocolEntries(this.constructionSite, this.issue)
        .then(entries => {
          this.issueProtocolEntries = entries
        })
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
