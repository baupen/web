<template>
  <span>
    <view-issue-button
        v-if="status === 'view'"
        ref="viewIssueButton"
        button-size="sm"
        :construction-manager-iri="constructionManagerIri" :issue="issue" :craftsmen="craftsmen"
        :construction-managers="constructionManagers" :map="mapContainer?.entity" :construction-site="constructionSite"
        :map-parent-names="mapContainer?.mapParentNames"/>
    <button v-else :disabled="status === 'loading'"
            class="btn btn-sm btn-outline-secondary" @click="load">
      <font-awesome-icon :icon="['far', 'dot-circle']"/>
    </button>
  </span>
</template>

<script>

import ViewIssueButton from "./ViewIssueButton.vue";
import {api} from "../../domain/api";

export default {
  components: {
    ViewIssueButton,
  },
  data() {
    return {
      status: 'initial',
      issue: null,
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    mapContainersLookup: {
      type: Object,
      required: true
    },
    constructionManagers: {
      type: Array,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true,
    },
    issueId: {
      type: String,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: true
    },
  },
  computed: {
    mapContainer: function () {
      if (!this.issue) {
        return null
      }

      return this.mapContainersLookup[this.issue.map]
    },
  },
  methods: {
    load: async function () {
      this.status = 'loading'
      this.issue = await api.getById("/api/issues/" + this.issueId)
      this.status = 'view'
      return this.$nextTick(() => {
        this.$refs.viewIssueButton.$el.nextElementSibling.click()
      })
    }
  }
}
</script>

