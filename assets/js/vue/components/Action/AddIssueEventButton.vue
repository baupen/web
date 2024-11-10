<template>
  <button-with-modal-confirm
      :title="$t('_action.add_issue_event.title')"
      :button-disabled="posting" :can-confirm="canConfirm"
      @confirm="confirm"
  >
    <slot name="before-form"/>
    <div class="mb-3">
      <custom-radio-field
          for-id="issue-event-type-text"
          :label="$t('_action.add_issue_event.type_text')">
        <input
            id="issue-event-type-text" class="form-check-input" type="radio"
            name="issue-event-type" value="TEXT"
            v-model="entryType">
      </custom-radio-field>

      <custom-radio-field
          for-id="issue-event-type-image"
          :label="$t('_action.add_issue_event.type_image')">
        <input
            id="issue-event-type-image" class="form-check-input" type="radio"
            name="issue-event-type" value="IMAGE"
            v-model="entryType">
      </custom-radio-field>

      <custom-radio-field
          for-id="issue-event-type-file"
          :label="$t('_action.add_issue_event.type_file')">
        <input
            id="issue-event-type-file" class="form-check-input" type="radio"
            name="issue-event-type" value="FILE"
            v-model="entryType">
      </custom-radio-field>
    </div>

    <template v-if="entryType === 'TEXT'">
      <issue-event-text-form @update="post = $event" :template="staleTemplate" :text-mode="true"/>
    </template>

    <template v-if="entryType === 'IMAGE'">
      <image-form @update="image = $event"/>
      <issue-event-text-form @update="post = $event" :template="staleTemplate" :text-mode="false"/>
    </template>
    <template v-if="entryType === 'FILE'">
      <file-form @update="file = $event"/>
      <issue-event-text-form @update="post = $event" :template="staleTemplate" :text-mode="false"/>
    </template>

  </button-with-modal-confirm>
</template>

<script>

import {api, iriToId} from '../../services/api'
import IssueEventTextForm from "../Form/IssueEventTextForm.vue";
import ButtonWithModalConfirm from "../Library/Behaviour/ButtonWithModalConfirm.vue";
import MapForm from "../Form/MapForm.vue";
import FileForm from "../Form/FileForm.vue";
import ImageForm from "../Form/ImageForm.vue";
import CustomRadioField from "../Library/FormLayout/CustomRadioField.vue";

export default {
  components: {
    CustomRadioField,
    ImageForm,
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
        ...this.post
      })

      const successMessage = this.$t('_action.add_issue_event.added');
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
