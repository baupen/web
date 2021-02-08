<template>
  <div class="card">
    <div class="card-body bg-light-gray p-2">
      <map-render-lightbox :map="map" :issues="issues" :preview="true" />
    </div>
  </div>
</template>

<script>

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
    map: {
      type: Object,
      required: true
    },
    issues: {
      type: Array,
      required: true
    },
  },
  computed: {
    overdueIssues: function () {
      const now = Date.now()

      return this.issues.filter(i => Date.parse(i.deadline) < now)
    },
  }
}
</script>
