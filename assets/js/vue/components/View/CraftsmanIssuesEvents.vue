<template>
  <loading-indicator-secondary :spin="issueEvents === null && constructionSiteIssueEvents == null">
    <add-issue-event-button
        :authority-iri="authorityIri" :root="craftsman" :construction-site="constructionSite"
        @added="issueEvents.push($event)"/>

    <div class="mt-3" v-if="orderedIssueEvents.length">
      <issue-event-row
          v-for="(entry, index) in orderedIssueEvents" :key="entry['@id']"
          :last="index+1 === orderedIssueEvents.length"
          :issue-event="entry"
          :root="getRoot(entry)"
          :is-context="!(issueEvents.includes(entry))"
          :authority-iri="authorityIri"
          :created-by="responsiblesLookup[entry['createdBy']]"
          :last-changed-by="responsiblesLookup[entry['lastChangedBy']]"
      />
    </div>
  </loading-indicator-secondary>
</template>

<script>

import {api, iriToId} from "../../services/api";
import LoadingIndicatorSecondary from "../Library/View/LoadingIndicatorSecondary.vue";
import AddIssueEventButton from "../Action/AddIssueEventButton.vue";
import {orderIssueEvents} from "../../services/sorters";
import IssueEventRow from "./IssueEventRow.vue";

export default {
  components: {
    IssueEventRow,
    AddIssueEventButton,
    LoadingIndicatorSecondary,
  },
  data() {
    return {
      issueEvents: null,
      constructionSiteIssueEvents: null,
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
    orderedIssueEvents: function () {
      const issueEvents = [...(this.issueEvents ?? []), ...(this.constructionSiteIssueEvents ?? [])]
          .filter(entry => !entry.isDeleted)
      orderIssueEvents(issueEvents)
      return issueEvents
    }
  },
  methods: {
    getRoot: function (issueEvent) {
      if (this.constructionSiteIssueEvents?.includes(issueEvent)) {
        return this.constructionSite
      }

      return this.craftsman
    },
  },
  mounted() {
    api.getIssueEvents(this.constructionSite, this.craftsman)
        .then(entries => {
          this.issueEvents = entries
        })
    api.getIssueEvents(this.constructionSite, this.constructionSite, true)
        .then(entries => {
          this.constructionSiteIssueEvents = entries
        })
  }
}
</script>

