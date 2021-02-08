<template>
  <div>
    <div v-for="mapIssues in groupedByMapIssues" :key="mapIssues.entity['@id']" class="mt-5">
      <h2>
        <span class="text-secondary"><small>{{ getMapContext(mapIssues) }}</small></span>
        {{ mapIssues.entity.name }}
      </h2>
      <masonry
          :cols="{default: 5, 1720: 4, 1290: 3, 860: 2, 430: 1}"
          :gutter="{default: '10px'}"
          class="mt-2">
        <div class="grid-item mb-2">
          <map-issue-overview-card :map="mapIssues.entity" :issues="mapIssues.issues.map(e => e.issue)" />
        </div>
        <div class="grid-item mb-2" v-for="issue in mapIssues.issues" :key="issue['@id']">
          <issue-resolve-card
              :issue="issue.issue"
              :created-by-construction-manager="issue.createdByConstructionManager" :map="mapIssues.entity"
              :craftsman-iri="craftsman['@id']" />
        </div>
      </masonry>
    </div>
  </div>
</template>

<script>

import ConstructionSitesEnterMasonryCard from './ConstructionSiteEnterCard'
import Masonry from '../Library/Behaviour/Masonry'
import IssueResolveCard from './IssueResolveCard'
import { createEntityIdLookup } from '../../services/algorithms'
import { mapTransformer } from '../../services/transformers'
import { mapFormatter } from '../../services/formatters'
import MapIssueOverviewCard from './MapIssueOverviewCard'

export default {
  components: {
    MapIssueOverviewCard,
    IssueResolveCard,
    Masonry,
    ConstructionSitesEnterMasonryCard
  },
  props: {
    constructionManagers: {
      type: Object,
      required: true
    },
    maps: {
      type: Object,
      required: true
    },
    issues: {
      type: Array,
      required: true
    },
    craftsman: {
      type: Object,
      required: true
    }
  },
  computed: {
    groupedByMapIssues: function () {
      const constructionManagerIdLookup = createEntityIdLookup(this.constructionManagers)

      let issueByMap = {}
      this.issues.forEach(issue => {
        if (!(issue.map in issueByMap)) {
          issueByMap[issue.map] = []
        }
        issueByMap[issue.map].push({
          issue,
          createdByConstructionManager: constructionManagerIdLookup[issue.createdBy]
        })
      })

      const flatMaps = mapTransformer.flatHierarchy(this.maps)
      const flatMapsWithIssues = flatMaps.map(m => Object.assign(m, {
        issues: issueByMap[m.entity['@id']]
      }))

      return flatMapsWithIssues.filter(entry => entry.issues)
    }
  },
  methods: {
    getMapContext: function (mapContainer) {
      let parentList = ''
      let currentContainer = mapContainer
      while (currentContainer.parent) {
        parentList = currentContainer.parent.name + ' > ' + parentList
        currentContainer = currentContainer.parent
      }
      return parentList
    }
  }
}
</script>
