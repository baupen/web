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
          <div class="card">
            <div class="card-body bg-light-gray p-2">
              <map-render-lightbox
                  :preview="true"
                  :construction-site="constructionSite" :map="mapIssues.entity" :craftsman="craftsman" :state="2" />
            </div>
          </div>
        </div>
        <div class="grid-item mb-2" v-for="issue in mapIssues.issues" :key="issue['@id']">
          <issue-resolve-card
              :issue="issue.issue"
              :construction-site="constructionSite" :craftsman="craftsman" :map="mapIssues.entity"
              :created-by-construction-manager="issue.createdByConstructionManager" />
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
import MapRenderLightbox from './MapRenderLightbox'

export default {
  components: {
    MapRenderLightbox,
    IssueResolveCard,
    Masonry,
    ConstructionSitesEnterMasonryCard
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
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
