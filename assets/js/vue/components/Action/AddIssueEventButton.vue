<template>
  <button-with-modal-confirm
      :title="title" :color="color"
      :button-disabled="posting" :can-confirm="canConfirm"
      @confirm="confirm"
  >
    <p class="alert alert-info" v-if="rootIsConstructionSite">
      {{ $t('_action.add_issue_event.adds_event_to_all_issues') }}
    </p>
    <p class="alert alert-info" v-else-if="rootIsCraftsman">
      {{ $t('_action.add_issue_event.adds_event_to_all_craftsman_issues') }}
    </p>

    <p class="alert alert-info" v-if="authorityIsCraftsman">
      {{ $t('_action.add_issue_event.adds_event_to_issue') }}
    </p>

    <ul class="nav nav-tabs" id="export-type-settings">
      <li class="nav-item">
        <a class="nav-link" :class="{'active': entryType === 'TEXT'}" @click="entryType = 'TEXT'">
          {{ $t('_action.add_issue_event.type_text') }}
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{'active': entryType === 'IMAGE'}" @click="entryType = 'IMAGE'">
          {{ $t('_action.add_issue_event.type_image') }}
        </a>
      </li>
      <li class="nav-item">
        <a class="nav-link" :class="{'active': entryType === 'FILE'}" @click="entryType = 'FILE'">
          {{ $t('_action.add_issue_event.type_file') }}
        </a>
      </li>
    </ul>
    <div class="tab-content p-3 border border-top-0">
      <div class="tab-pane fade" :class="{'show active': entryType === 'TEXT'}">
        <issue-event-text-form @update="post = $event" :template="staleTemplate" :text-mode="true"
                               :hide-timestamp="authorityIsCraftsman"/>
      </div>
      <div class="tab-pane fade" :class="{'show active': entryType === 'IMAGE'}">
        <mobile-image-form v-if="authorityIsCraftsman" @update="image = $event"/>
        <image-form v-else @update="image = $event"/>
        <issue-event-text-form @update="post = $event" :template="staleTemplate" :text-mode="false"
                               :hide-timestamp="authorityIsCraftsman"/>
      </div>
      <div class="tab-pane fade" :class="{'show active': entryType === 'FILE'}">
        <file-form @update="file = $event"/>
        <issue-event-text-form @update="post = $event" :template="staleTemplate" :text-mode="false"
                               :hide-timestamp="authorityIsCraftsman"/>
      </div>
    </div>
  </button-with-modal-confirm>
</template>

<script>

import {api, iriToId} from '../../services/api'
import IssueEventTextForm from "../Form/IssueEventTextForm.vue";
import ButtonWithModalConfirm from "../Library/Behaviour/ButtonWithModalConfirm.vue";
import MapForm from "../Form/MapForm.vue";
import FileForm from "../Form/FileForm.vue";
import ImageForm from "../Form/ImageForm.vue";
import MobileImageForm from "../Form/MobileImageForm.vue";
import CustomRadioField from "../Library/FormLayout/CustomRadioField.vue";
import ExportIssuesReportView from "./ExportIssuesReportView.vue";
import ExportIssuesLinkView from "./ExportIssuesLinkView.vue";

export default {
  components: {
    ExportIssuesLinkView, ExportIssuesReportView,
    CustomRadioField,
    ImageForm,
    MobileImageForm,
    FileForm, MapForm,
    ButtonWithModalConfirm,
    IssueEventTextForm,
  },
  emits: ['added'],
  data() {
    return {
      entryType: 'TEXT',

      post: null,
      staleTemplate: null,

      image: null,
      file: null,

      posting: false,
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    root: {
      type: Object,
      required: true
    },
    authorityIri: {
      type: String,
      required: true
    },
    color: {
      type: String,
      default: 'primary'
    }
  },
  watch: {
    entryType: function () {
      this.staleTemplate = this.post
    }
  },
  computed: {
    canConfirm: function () {
      return !!this.post && (this.entryType === 'TEXT') ||
          (this.entryType === 'IMAGE' && !!this.image) ||
          (this.entryType === 'FILE' && !!this.file)
    },
    rootIsConstructionSite: function () {
      return this.root['@id'].includes('construction_sites')
    },
    rootIsCraftsman: function () {
      return this.root['@id'].includes('craftsmen')
    },
    authorityIsCraftsman: function () {
      return this.authorityIri.includes('craftsmen')
    },
    title: function () {
      return this.authorityIsCraftsman ?
          this.$t('_action.add_issue_event.title_craftsman') :
          this.$t('_action.add_issue_event.title')
    }
  },
  methods: {
    confirm: function () {
      this.posting = true
      let payload = Object.assign({
        constructionSite: this.constructionSite["@id"],
        root: iriToId(this.root['@id']),
        type: this.entryType,
        createdBy: iriToId(this.authorityIri),
        lastChangedBy: iriToId(this.authorityIri),
        ...this.post
      })

      const successMessage = this.authorityIsCraftsman ?
          this.$t('_action.add_issue_event.added_craftsman') :
          this.$t('_action.add_issue_event.added');

      if (this.entryType === 'TEXT') {
        api.postIssueEvent(payload, successMessage)
            .then(issueEvent => {
              this.posting = false
              this.$emit('added', issueEvent)
            })
        return
      }

      api.postIssueEvent(payload)
          .then(issueEvent => {
            const file = this.entryType === 'IMAGE' ? this.image : this.file;
            api.postIssueEventFile(issueEvent, file, successMessage)
                .then(_ => {
                  this.file = null
                  this.posting = false
                  this.$emit('added', issueEvent)
                })
          })
    }
  }
}
</script>
