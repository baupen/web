<template>
  <div class="card">
    <div class="card-body">
      <div class="row g-4">
        <div class="col-md-6">
          <div class="w-50 d-inline-block pe-1">
            <issue-render-lightbox
                :preview="true"
                :construction-site="constructionSite" :map="map" :issue="issue"/>
          </div>
          <div class="w-50 d-inline-block ps-1">
            <image-lightbox
                v-if="issue.imageUrl"
                :preview="true"
                :src="issue.imageUrl" :subject="issue.number + ': ' + issue.description"/>
          </div>
        </div>
        <div class="col-md-6">
          <p v-if="issue.description" class="bg-light-gray p-2">
            {{ issue.description }}
          </p>
          <p v-if="issue.deadline">
            <b>{{ $t('issue.deadline') }}</b>:
            <date-human-readable :value="issue.deadline"/>
            <br/>
            <span v-if="isOverdue" class="badge bg-danger">{{ $t('issue.state.overdue') }}</span>
          </p>
          <div class="row g-2" v-if="craftsman.canEdit" >
            <div :class="{'col-auto': addedIssueEvents.length === 0, 'col-12': addedIssueEvents.length > 0}">
              <add-issue-event-button
                  v-if="!issue.resolvedAt"
                  :authority-iri="craftsman['@id']" :root="issue"
                  :construction-site="constructionSite" color="secondary"
                  @added="addedIssueEvents.push($event)"/>

              <div class="mt-2 mb-4" v-if="addedIssueEvents.length">
                <issue-event-row
                    v-for="(entry, index) in addedIssueEvents" :key="entry['@id']"
                    :last="index+1 === addedIssueEvents.length"
                    :issue-event="entry"
                    :root="issue"
                    :authority-iri="craftsman['@id']"
                    :created-by="responsiblesLookup[entry['createdBy']]"
                    :last-changed-by="responsiblesLookup[entry['lastChangedBy']]"
                />
              </div>
            </div>
            <div class="col-auto">
              <resolve-issue-button :issue="issue" :craftsman="craftsman"/>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="card-footer">
      <small class="text-muted">
        #{{ issue.number }} |
        <date-time-human-readable :value="issue.createdAt"/>
        <span v-if="createdByConstructionManager">
        |
        {{ createdByConstructionManagerName }}
          <a class="ps-1" :href="createdByConstructionManagerEmailHref">
            <font-awesome-icon :icon="['fal', 'envelope-open']"/>
          </a>
        </span>
      </small>
    </div>
  </div>
</template>

<script>

import {constructionManagerFormatter} from '../../services/formatters'
import ImageLightbox from './ImageLightbox'
import ResolveIssueButton from '../Action/ResolveIssueButton'
import DateTimeHumanReadable from '../Library/View/DateTimeHumanReadable'
import DateHumanReadable from '../Library/View/DateHumanReadable'
import {issueTransformer} from '../../services/transformers'
import IssueRenderLightbox from './IssueRenderLightbox'
import AddIssueEventButton from "../Action/AddIssueEventButton.vue";
import IssueEventRow from "./IssueEventRow.vue";
import {iriToId} from "../../services/api";

export default {
  components: {
    IssueEventRow,
    AddIssueEventButton,
    IssueRenderLightbox,
    DateHumanReadable,
    DateTimeHumanReadable,
    ResolveIssueButton,
    ImageLightbox
  },
  data() {
    return {
      addedIssueEvents: []
    }
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
    responsiblesLookup: function () {
      let responsiblesLookup = {}
      responsiblesLookup[iriToId(this.craftsman['@id'])] = this.craftsman
      this.constructionManagers.forEach(cm => responsiblesLookup[iriToId(cm['@id'])] = cm)

      return responsiblesLookup
    },
    createdByConstructionManager: function () {
      return this.responsiblesLookup[iriToId(this.issue.createdBy)]
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
