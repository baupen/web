<template>
  <button-with-modal-confirm
      :title="$t('actions.view_issue')" :color="stateColor"
      :confirm-title="$t('actions.close')">

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
            class="ml-2"
            icon="star"
            :value="issue.isMarked" />
        <toggle-icon
            class="ml-2"
            icon="user-check"
            :value="issue.wasAddedWithClient" />
      </h5>
    </template>

    <div>
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
        {{ craftsman.trade }}<br />
        <span class="text-muted">{{ craftsman.company }}</span>
      </div>
    </div>
    <div class="row mt-3" v-if="issue.deadline">
      <div class="col-3">
        {{ $t('issue.deadline') }}
      </div>
      <div class="col">
        <date-human-readable :value="issue.deadline" /> <br/>
        <span v-if="isOverdue" class="badge badge-danger">{{ $t('issue.state.overdue') }}</span>
      </div>
    </div>

    <hr />

    <div class="row">
      <div class="col-md-3">
        <p class="m-0 state-icon text-primary">
          <font-awesome-icon :icon="['far', 'plus-circle']" />
        </p>
      </div>
      <div class="col">
        {{createdByName}}<br/>
        <date-time-human-readable :value="issue.createdAt" />
      </div>
    </div>

    <div class="row mt-3" v-if="isRegistered">
      <div class="col-md-3">
        <p class="m-0 state-icon text-primary">
          <font-awesome-icon :icon="['far', 'dot-circle']" />
          <span class="state-joiner" />
        </p>
      </div>
      <div class="col">
        {{registeredByName}}<br/>
        <date-time-human-readable :value="issue.registeredAt" />
      </div>
    </div>

    <div class="row mt-3" v-if="isResolved">
      <div class="col-md-3">
        <p class="m-0 state-icon text-orange">
          <font-awesome-icon :icon="['far', 'exclamation-circle']" />
          <span class="state-joiner" />
        </p>
      </div>
      <div class="col">
        {{resolvedByName}}<br/>
        <date-time-human-readable :value="issue.resolvedAt" />
      </div>
    </div>

    <div class="row mt-3" v-if="isClosed">
      <div class="col-md-3">
        <p class="m-0 state-icon text-success">
          <font-awesome-icon :icon="['far', 'check-circle']" />
          <span class="state-joiner" />
        </p>
      </div>
      <div class="col">
        {{closedByName}}<br/>
        <date-time-human-readable :value="issue.closedAt" />
      </div>
    </div>

  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import MapRenderLightbox from '../View/MapRenderLightbox'
import ImageLightbox from '../View/ImageLightbox'
import DateHumanReadable from '../Library/View/DateHumanReadable'
import { issueTransformer } from '../../services/transformers'
import ToggleIcon from '../Library/View/ToggleIcon'
import { constructionManagerFormatter } from '../../services/formatters'
import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'

export default {
  components: {
    DateTimeHumanReadable,
    ToggleIcon,
    DateHumanReadable,
    ImageLightbox,
    MapRenderLightbox,
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
    craftsman: {
      type: Object,
    },
    resolvedBy: {
      type: Object,
    },
    constructionManagers: {
      type: Object,
      required: true
    },
    issue: {
      type: Object,
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
      return this.resolvedBy ? this.resolvedBy.company : null
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

<style scoped="true">
.state-icon {
  font-size: 2em;
  position: relative;
  text-align: right;
}

.state-joiner {
  background-color: rgba(0, 0, 0, 0.1);
  bottom: calc(1.25em + 1px);
  height: 1em;
  position: absolute;
  right: calc(0.5em - 1px);
  width: 2px;
}
</style>
