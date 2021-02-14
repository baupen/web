<template>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div v-if="issue.positionX" :class="{'col-md-3': issue.imageUrl, 'col-md-6': !issue.imageUrl}">
          <map-render-lightbox
              :preview="true"
              :construction-site="constructionSite" :map="map" :craftsman="craftsman" :issue="issue" />
        </div>
        <div v-if="issue.imageUrl" :class="{'col-md-3': issue.positionX, 'col-md-6': !issue.positionX}">
          <image-lightbox
              :preview="true"
              :src="issue.imageUrl" :subject="issue.number + ': ' + issue.description" />
        </div>
        <div class="col-md-6">
          <p class="bg-light-gray p-2">
            {{ issue.description }}
          </p>
          <p v-if="issue.deadline">
            <b>{{ $t('issue.deadline') }}</b>:
            <date-human-readable :value="issue.deadline" /> <br/>
            <span v-if="overdue" class="badge badge-danger">{{ $t('issue.state.overdue') }}</span>
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
    overdue: function () {
      if (!this.issue.deadline) {
        return false
      }

      const deadline = Date.parse(this.issue.deadline)
      const now = Date.now()
      return deadline < now
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
