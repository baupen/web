<template>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6">
          <div class="w-50 d-inline-block pe-1">
            <issue-render-lightbox
                :preview="true"
                :construction-site="constructionSite" :map="map" :issue="issue" />
          </div>
          <div class="w-50 d-inline-block ps-1">
            <image-lightbox
                v-if="issue.imageUrl"
                :preview="true"
                :src="issue.imageUrl" :subject="issue.number + ': ' + issue.description" />
          </div>
        </div>
        <div class="col-md-6">
          <p v-if="issue.description" class="bg-light-gray p-2 mt-2 mt-md-0">
            {{ issue.description }}
          </p>
          <p v-if="issue.deadline">
            <b>{{ $t('issue.deadline') }}</b>:
            <date-human-readable :value="issue.deadline" />
            <br />
            <span v-if="isOverdue" class="badge bg-danger">{{ $t('issue.state.overdue') }}</span>
          </p>
          <resolve-issue-button v-if="craftsman.canEdit" :issue="issue" :craftsman="craftsman" />
        </div>
      </div>
    </div>
    <div class="card-footer">
      <small class="text-muted">
        #{{ issue.number }} |
        <date-time-human-readable :value="issue.createdAt" />
        <span v-if="createdByConstructionManager">
        |
        {{ createdByConstructionManagerName }}
          <a class="ps-1" :href="createdByConstructionManagerEmailHref">
            <font-awesome-icon :icon="['fal', 'envelope-open']" />
          </a>
        </span>
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
import { issueTransformer } from '../../services/transformers'
import IssueRenderLightbox from './IssueRenderLightbox'

export default {
  components: {
    IssueRenderLightbox,
    DateHumanReadable,
    DateTimeHumanReadable,
    ResolveIssueButton,
    ImageLightbox
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
    createdByConstructionManager: function () {
      return this.constructionManagers.find(m => m['@id'] === this.issue.createdBy)
    },
    createdByConstructionManagerName: function () {
      return constructionManagerFormatter.name(this.createdByConstructionManager)
    },
    createdByConstructionManagerEmailHref: function () {
      return "mailto:" + this.createdByConstructionManager.email + "?subject=" + this.constructionSite.name + ": #" + this.issue.number
    }
  }
}
</script>
