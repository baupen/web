<template>
  <loading-indicator-secondary
      :spin="issueEvents === null && craftsmanIssueEvents === null && constructionSiteIssueEvents == null">
    <add-issue-event-button
        :authority-iri="authorityIri" :root="issue" :construction-site="constructionSite"
        @added="issueEvents.push($event)"/>

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
import {sortIssueEvents} from "../../services/sorters";
import IssueEventRow from "./IssueEventRow.vue";

export default {
  components: {
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
    createdByCraftsmanFilter: {
      type: Object,
      default: null
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
      sortIssueEvents(issueEvents)
      return issueEvents
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
  },
  mounted() {
    if (this.createdByCraftsmanFilter) {
      const query = {
        'isDeleted': false,
        'createdBy': iriToId(this.createdByCraftsmanFilter['@id'])
      }
      api.getIssueEvents(this.constructionSite, this.issue, query)
          .then(entries => {
            this.issueEvents = entries
          })
    } else {
      api.getIssueEvents(this.constructionSite, this.issue)
          .then(entries => {
            this.issueEvents = entries
          })
      if (this.craftsman) {
        api.getIssueEvents(this.constructionSite, this.craftsman)
            .then(entries => {
              this.craftsmanIssueEvents = entries
            })
      }
      api.getIssueEvents(this.constructionSite, this.constructionSite)
          .then(entries => {
            this.constructionSiteIssueEvents = entries
          })
    }
  }
}
</script>
