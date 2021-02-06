<template>
  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col">
          <map-issue-render :src="map.fileUrl" :subject="issue.number + ' on ' + map.name" />
        </div>
        <div class="col">
          <image-lightbox :src="issue.imageUrl" :subject="issue.number + ': ' + issue.description" />
        </div>
      </div>
      <p>
        <b>{{issue.number}}: </b> {{issue.description}}
      </p>
      <resolve-issue-button :issue="issue" :craftsman-iri="craftsmanIri" />
    </div>
    <div class="card-footer">
      <small class="text-muted">
        <date-time-human-readable :value="issue.createdAt" /> |
        {{ createdByConstructionManagerName }}
      </small>
    </div>
  </div>
</template>

<script>

import { constructionManagerFormatter } from '../../services/formatters'
import ImageLightbox from './ImageLightbox'
import MapIssueRender from './MapIssueRender'
import ResolveIssueButton from '../Action/ResolveIssueButton'
import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'

export default {
  components: { DateTimeHumanReadable, ResolveIssueButton, MapIssueRender, ImageLightbox },
  props: {
    issue: {
      type: Object,
      required: true
    },
    map: {
      type: Object,
      required: true
    },
    createdByConstructionManager: {
      type: Object,
      required: true
    },
    craftsmanIri: {
      type: String,
      required: true
    }
  },
  computed: {
    createdByConstructionManagerName: function () {
      return constructionManagerFormatter.name(this.createdByConstructionManager);
    }
  }
}
</script>
