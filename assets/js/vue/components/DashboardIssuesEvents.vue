<template>
  <div class="card">
    <div class="card-body limited-height">
      <div class="loading-center" v-if="issueEvents === null">
        <loading-indicator-secondary/>
      </div>
      <template v-else>
        <add-issue-event-button
            :authority-iri="constructionManagerIri" :root="constructionSite"
            :construction-site="constructionSite"
            @added="issueEvents.push($event)" />

        <div class="mt-3" v-if="orderedIssueEvents.length">
          <issue-event-row
              v-for="(entry, index) in orderedIssueEvents" :key="entry['@id']"
              :last="index+1 === orderedIssueEvents.length"
              :issue-event="entry"
              :root="constructionSite"
              :authority-iri="constructionManagerIri"
              :created-by="responsiblesLookup[entry['createdBy']]"
              :last-changed-by="responsiblesLookup[entry['lastChangedBy']]"
          />
        </div>
      </template>
    </div>
  </div>
</template>

<script>

import LoadingIndicatorSecondary from "./Library/View/LoadingIndicatorSecondary.vue";
import {api, iriToId} from "../services/api";
import AddIssueEventButton from "./Action/AddIssueEventButton.vue";
import IssueEventRow from "./View/IssueEventRow.vue";
import {sortIssueEvents} from "../services/sorters";

export default {
  components: {
    AddIssueEventButton,
    IssueEventRow,
    LoadingIndicatorSecondary,
  },
  data() {
    return {
      issueEvents: null,
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
    orderedIssueEvents: function () {
      const issueEvents = this.issueEvents.filter(event => !event.isDeleted)
      sortIssueEvents(issueEvents)
      return issueEvents
    }
  },
  mounted() {
    api.getIssueEvents(this.constructionSite, this.constructionSite)
        .then(entries => {
          this.issueEvents = entries
        })
  }
}
</script>

<style scoped>
.limited-height {
  max-height: 30em;
  overflow-y: auto;
  overflow-x: hidden;
}

.loading-center > * {
  display: block;
  margin: 0 auto;
}
</style>
