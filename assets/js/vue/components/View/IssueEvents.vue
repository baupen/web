<template>
  <loading-indicator-secondary
      :spin="issueEvents === null && craftsmanIssueEvents === null && constructionSiteIssueEvents == null">
    <div class="d-flex align-items-center gap-3">
      <add-issue-event-button
          :authority-iri="authorityIri" :root="issue" :construction-site="constructionSite"
          @added="issueEvents.push($event)"/>
      <mark-issue-resolved-checkbox
          class="ms-5"
          :issue="issue"
          @marked="loadIssueEvents"
      />
      <mark-issue-closed-checkbox
          :issue="issue" :construction-manager-iri="authorityIri"
          @marked="loadIssueEvents"
      />
    </div>

    <div class="mt-3" v-if="orderedIssueEvents.length">
      <issue-event-row
          v-for="(entry, index) in orderedIssueEvents" :key="entry['@id']"
          :last="index+1 === orderedIssueEvents.length"
          :issue-event="entry"
          :root="getRoot(entry)"
          :authority-iri="authorityIri"
          :is-context="!issueEvents.includes(entry)"
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
import CustomCheckboxField from "../Library/FormLayout/CustomCheckboxField.vue";
import CustomCheckbox from "../Library/FormInput/CustomCheckbox.vue";
import {filterIssueEventsForIssue, orderIssueEvents} from "../../services/sorters";
import IssueEventRow from "./IssueEventRow.vue";
import MarkIssueResolvedCheckbox from "../Action/MarkIssueResolvedCheckbox.vue";
import MarkIssueClosedCheckbox from "../Action/MarkIssueClosedCheckbox.vue";

export default {
  components: {
    MarkIssueClosedCheckbox,
    MarkIssueResolvedCheckbox,
    IssueEventRow,
    CustomCheckbox,
    CustomCheckboxField,
    AddIssueEventButton,
    LoadingIndicatorSecondary,
  },
  data() {
    return {
      issueEvents: null,
      craftsmanIssueEvents: null,
      constructionSiteIssueEvents: null,

      refreshIteration: 0
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
    orderedIssueEvents: function () {
      const issueEvents = [...(this.issueEvents ?? []), ...(this.craftsmanIssueEvents ?? []), ...(this.constructionSiteIssueEvents ?? [])]
          .filter(entry => !entry.isDeleted)
      orderIssueEvents(issueEvents)
      return filterIssueEventsForIssue(issueEvents, this.issue['@id'])
    },
    craftsman: function () {
      return this.craftsmen.find(craftsman => craftsman['@id'] === this.issue.craftsman);
    }
  },
  methods: {
    getRoot: function (issueEvent) {
      if (this.craftsmanIssueEvents?.includes(issueEvent)) {
        return this.craftsman
      }
      if (this.constructionSiteIssueEvents?.includes(issueEvent)) {
        return this.constructionSite
      }

      return this.issue
    },
    loadIssueEvents: function () {
      const refreshIteration = ++this.refreshIteration
      api.getIssueEvents(this.constructionSite, this.issue)
          .then(entries => {
            if (refreshIteration === this.refreshIteration) {
              this.issueEvents = entries
            }
          })
    },
  },
  mounted() {
    this.loadIssueEvents()
    if (this.craftsman) {
      api.getIssueEvents(this.constructionSite, this.craftsman, true)
          .then(entries => {
            this.craftsmanIssueEvents = entries
          })
    }
    api.getIssueEvents(this.constructionSite, this.constructionSite, true)
        .then(entries => {
          this.constructionSiteIssueEvents = entries
        })
  }
}
</script>
