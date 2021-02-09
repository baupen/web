<template>
  <div class="card">
    <div class="card-body p-2">
      <div class="row mb-3">
        <div class="col"  :class="{'pr-2': issue.imageUrl}" v-if="issue.positionX">
          <map-render-lightbox :construction-site="constructionSite" :map="map" :craftsman="craftsman" :issue="issue" :preview="true" />
        </div>
        <div class="col" v-if="issue.imageUrl" :class="{'pl-2': issue.positionX}">
          <image-lightbox :src="issue.imageUrl" :subject="issue.number + ': ' + issue.description" :preview="true" />
        </div>
      </div>
      <p v-if="issue.description" class="bg-light-gray p-2">
        {{ issue.description }} <br/>
        <span v-if="issue.deadline">
          <b>{{ $t('issue.deadline') }}</b>:
          <date-human-readable :value="issue.deadline" />
          <span v-if="overdue" class="badge badge-danger ml-1">{{ $t('issue.state.overdue') }}</span>
        </span>
      </p>
      <resolve-issue-button :issue="issue" :craftsman="craftsman" />
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
        return '';
      }

      return constructionManagerFormatter.name(createdByConstructionManager)
    }
  }
}
</script>
