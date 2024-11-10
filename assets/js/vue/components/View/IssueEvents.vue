<template>
  <loading-indicator-secondary :spin="issueIssueEvents === null">
    <div class="row mb-2">
      <div class="col-3">
        {{ $t('issue_event._plural') }}
      </div>
      <div class="col">
        <add-issue-event-button :authority-iri="authorityIri" :root="issue" :construction-site="constructionSite"
                                   @added="newIssueEvents.push($event)"/>
      </div>
    </div>

    <issue-event v-for="(entry, index) in orderedIssueEvents" :id="entry['@id']"
                    :last="index+1 === orderedIssueEvents.length"
                    :issue-event="entry"
                    :root="getRoot(entry)"
                    :is-context="!(issueIssueEvents.includes(entry) || newIssueEvents.includes(entry))"
                    :is-removable="newIssueEvents.includes(entry)"
                    :created-by="responsiblesLookup[entry['createdBy']]"
    />
  </loading-indicator-secondary>
</template>

<script>

import {api, iriToId} from "../../services/api";
import IssueEvent from "./IssueEvent.vue";
import LoadingIndicatorSecondary from "../Library/View/LoadingIndicatorSecondary.vue";
import AddIssueEventButton from "../Action/AddIssueEventButton.vue";
import CustomCheckboxField from "../Library/FormLayout/CustomCheckboxField.vue";
import CustomCheckbox from "../Library/FormInput/CustomCheckbox.vue";
import {sortIssueEvents} from "../../services/sorters";

export default {
  components: {
    CustomCheckbox,
    CustomCheckboxField,
    AddIssueEventButton,
    LoadingIndicatorSecondary,
    IssueEvent,
  },
  data() {
    return {
      newIssueEvents: [],
      issueIssueEvents: null,
      craftsmanIssueEvents: null,
      constructionSiteIssueEvents: null,
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
    orderedIssueEvents: function () {
      const issueEvents = [...this.newIssueEvents, ...(this.issueIssueEvents ?? []), ...(this.craftsmanIssueEvents ?? []), ...(this.constructionSiteIssueEvents ?? [])]
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
    api.getIssueEvents(this.constructionSite, this.issue)
        .then(entries => {
          this.issueIssueEvents = entries
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
</script>
