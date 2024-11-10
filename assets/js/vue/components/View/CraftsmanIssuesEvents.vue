<template>
  <loading-indicator-secondary :spin="craftsmanIssueEvents === null">
    <div class="row mb-2">
      <div class="col-3">
        {{ $t('issue_event._plural') }}
      </div>
      <div class="col">
        <add-issue-event-button :authority-iri="authorityIri" :root="craftsman" :construction-site="constructionSite"
                                   @added="newIssueEvents.push($event)"/>
      </div>
    </div>

    <issue-event-row v-for="(entry, index) in orderedIssueEvents" :id="entry['@id']"
                    :last="index+1 === orderedIssueEvents.length"
                    :issue-event="entry"
                    :root="getRoot(entry)"
                    :is-context="!(craftsmanIssueEvents.includes(entry) || newIssueEvents.includes(entry))"
                    :created-by="responsiblesLookup[entry['createdBy']]"
    />
  </loading-indicator-secondary>
</template>

<script>

import {api, iriToId} from "../../services/api";
import LoadingIndicatorSecondary from "../Library/View/LoadingIndicatorSecondary.vue";
import AddIssueEventButton from "../Action/AddIssueEventButton.vue";
import {sortIssueEvents} from "../../services/sorters";
import IssueEventRow from "./IssueEventRow.vue";

export default {
  components: {
    IssueEventRow,
    AddIssueEventButton,
    LoadingIndicatorSecondary,
  },
  data() {
    return {
      newIssueEvents: [],
      craftsmanIssueEvents: null,
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
      const issueEvents = [...this.newIssueEvents, ...(this.craftsmanIssueEvents ?? []), ...(this.constructionSiteIssueEvents ?? [])]
          .filter(entry => !entry.isDeleted)
      sortIssueEvents(issueEvents)
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
          this.craftsmanIssueEvents = entries
        })
    api.getIssueEvents(this.constructionSite, this.constructionSite)
        .then(entries => {
          this.constructionSiteIssueEvents = entries
        })
  }
}
</script>
