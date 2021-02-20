<template>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="w-50 d-inline-block pr-1">
            <map-render-lightbox
                :preview="true"
                :construction-site="constructionSite" :map="map" :issue="issue" />
          </div>
          <div class="w-50 d-inline-block pl-1">
            <image-lightbox
                v-if="issue.imageUrl"
                :preview="true"
                :src="issue.imageUrl" :subject="issue.number + ': ' + issue.description" />
          </div>
        </div>
        <div class="col-md-6">
          <p class="bg-light-gray p-2 mt-2 mt-md-0">
            {{ issue.description }}
          </p>
          <p v-if="issue.deadline">
            <b>{{ $t('issue.deadline') }}</b>:
            <date-human-readable :value="issue.deadline" />
            <br />
            <span v-if="isOverdue" class="badge badge-danger">{{ $t('issue.state.overdue') }}</span>
          </p>
          <resolve-issue-button :issue="issue" :craftsman="craftsman" />
        </div>
      </div>
    </div>
    <div class="card-footer">
      <small class="text-muted">
        #{{ issue.number }} |
        <date-time-human-readable :value="issue.createdAt" />
        |
        {{ createdByConstructionManagerName }}
      </small>
    </div>
  </div>
</template>

<script>

import { constructionManagerFormatter } from '../../services/formatters'
import ImageLightbox from './ImageLightbox'
import ResolveIssueButton from '../Action/ResolveIssueButton'
import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'
import DateHumanReadable from '../Library/View/DateHumanReadable'
import MapRenderLightbox from './MapRenderLightbox'
import { issueTransformer } from '../../services/transformers'

export default {
  components: {
    DateHumanReadable,
    DateTimeHumanReadable,
    ResolveIssueButton,
    ImageLightbox,
    MapRenderLightbox
  },
  props: {
    constructionManagers: {
      type: Array,
      required: true
    },
    constructionSite: {
      type: Object,
      required: true
    },
    map: {
      type: Object,
      required: true
    },
    craftsman: {
      type: Object,
      required: true
    },
    issue: {
      type: Object,
      required: true
    },
  },
  computed: {
    isOverdue: function () {
       return issueTransformer.isOverdue(this.issue)
    },
    createdByConstructionManagerName: function () {
      const createdByConstructionManager = this.constructionManagers.find(m => m['@id'] === this.issue.createdBy)
      if (!createdByConstructionManager) {
        return ''
      }

      return constructionManagerFormatter.name(createdByConstructionManager)
    }
  }
}
</script>
