<template>
  <button-with-modal-confirm
      modal-size="mmd"
      :title="$t('_action.view_issue.title')" :color="stateColor">

    <template v-slot:footer>
      <span class="d-none"></span>
    </template>

    <template v-slot:button-content>
      <font-awesome-icon v-if="isClosed" :icon="['far', 'check-circle']" />
      <font-awesome-icon v-else-if="isResolved" :icon="['far', 'exclamation-circle']" />
      <font-awesome-icon v-else-if="isRegistered" :icon="['far', 'dot-circle']" />
      <font-awesome-icon v-else :icon="['far', 'plus-circle']" />
    </template>

    <template v-slot:header>
      <h5 class="modal-title">
        <span>
          #{{issue.number}}
        </span>
        <toggle-icon
            class="ms-2"
            icon="star"
            :value="issue.isMarked" />
        <toggle-icon
            class="ms-2"
            icon="user-check"
            :value="issue.wasAddedWithClient" />
      </h5>
    </template>

    <div class="mh-30em">
      <div class="mw-50 d-inline-block pe-1">
        <issue-render-lightbox
            :preview="true"
            :construction-site="constructionSite" :map="map" :issue="issue" />
      </div>
      <div class="mw-50 d-inline-block ps-1">
        <image-lightbox
            v-if="issue.imageUrl"
            :preview="true"
            :src="issue.imageUrl" :subject="issue.number + ': ' + issue.description" />
      </div>
    </div>

    <p v-if="issue.description" class="bg-light-gray p-2 mt-3 mb-0">
      {{ issue.description }}
    </p>
    <div class="row mt-3" v-if="map">
      <div class="col-3">
        {{ $t('map._name') }}
      </div>
      <div class="col">
        {{ map.name }}<br />
        <span class="text-muted">{{ mapParentNames.join(' > ') }}</span>
      </div>
    </div>
    <div class="row mt-3" v-if="craftsman">
      <div class="col-3">
        {{ $t('craftsman._name') }}
      </div>
      <div class="col">
        {{ craftsman.company }}<br />
        <span class="text-muted">{{ craftsman.trade }}</span>
      </div>
    </div>
    <div class="row mt-3" v-if="issue.deadline">
      <div class="col-3">
        {{ $t('issue.deadline') }}
      </div>
      <div class="col">
        <date-human-readable :value="issue.deadline" /> <br/>
        <span v-if="isOverdue" class="badge bg-danger">{{ $t('issue.state.overdue') }}</span>
      </div>
    </div>

    <hr/>

    <issue-events
        :construction-site="constructionSite" :issue="issue"
        :craftsmen="craftsmen" :construction-managers="constructionManagers"
        :authority-iri="constructionManagerIri"
    />

    <hr/>

    <p class="mb-0 text-secondary">
      {{$t("issue.last_changed_at")}}: <date-time-human-readable :value="issue.lastChangedAt" />
    </p>

  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import ImageLightbox from '../View/ImageLightbox'
import DateHumanReadable from '../Library/View/DateHumanReadable'
import { issueTransformer } from '../../services/transformers'
import ToggleIcon from '../Library/View/ToggleIcon'
import { constructionManagerFormatter } from '../../services/formatters'
import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'
import IssueRenderLightbox from '../View/IssueRenderLightbox'
import IssueEvents from "../View/IssueEvents.vue";

export default {
  components: {
    IssueEvents,
    IssueRenderLightbox,
    DateTimeHumanReadable,
    ToggleIcon,
    DateHumanReadable,
    ImageLightbox,
    ButtonWithModalConfirm
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    map: {
      type: Object,
      required: true
    },
    mapParentNames: {
      type: Array,
    },
    constructionManagers: {
      type: Array,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true,
    },
    issue: {
      type: Object,
      required: true
    },
    constructionManagerIri: {
      type: String,
      required: true
    },
  },
  computed: {
    constructionManagerLookup: function () {
      let constructionManagerLookup = {}
      this.constructionManagers.forEach(cm => constructionManagerLookup[cm['@id']] = cm)

      return constructionManagerLookup
    },
    isOverdue: function () {
      return issueTransformer.isOverdue(this.issue)
    },
    craftsman: function () {
      return this.craftsmen.find(craftsman => craftsman['@id'] === this.issue.craftsman)
    },
    createdByName: function () {
      const createdBy = this.constructionManagerLookup[this.issue.createdBy]
      return createdBy ? constructionManagerFormatter.name(createdBy) : ""
    },
    registeredBy: function () {
      return this.constructionManagerLookup[this.issue.registeredBy]
    },
    registeredByName: function () {
      const registeredBy = this.constructionManagerLookup[this.issue.registeredBy]
      return registeredBy ? constructionManagerFormatter.name(registeredBy) : ""
    },
    resolvedByName: function () {
      const resolvedBy = this.craftsmen.find(craftsman => craftsman['@id'] === this.issue.resolvedBy)
      return resolvedBy?.company
    },
    closedBy: function () {
      return this.constructionManagerLookup[this.issue.closedBy]
    },
    closedByName: function () {
      const closedBy = this.constructionManagerLookup[this.issue.closedBy]
      return closedBy ? constructionManagerFormatter.name(closedBy) : ""
    },
    stateColor: function () {
      if (this.isClosed) {
        return 'success'
      }

      if (this.isResolved) {
        return 'orange'
      }

      return 'primary'
    },
    isResolved: function () {
      return !!this.issue.resolvedBy
    },
    isClosed: function () {
      return !!this.issue.closedBy
    },
    isRegistered: function () {
      return !!this.issue.registeredBy
    }
  }
}
</script>

