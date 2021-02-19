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

  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import MapRenderLightbox from '../View/MapRenderLightbox'
import ImageLightbox from '../View/ImageLightbox'
import DateHumanReadable from '../Library/View/DateHumanReadable'
import { issueTransformer } from '../../services/transformers'
import ToggleIcon from '../Library/View/ToggleIcon'

export default {
  components: {
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
    createdBy: function () {
      return this.constructionManagerLookup[this.issue.createdBy]
    },
    registeredBy: function () {
      return this.constructionManagerLookup[this.issue.registeredBy]
    },
    closedBy: function () {
      return this.constructionManagerLookup[this.issue.closedBy]
    },
    stateColor: function () {
      if (this.isClosed) {
        return 'success'
      }

      if (this.isResolved) {
        return 'warning'
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
