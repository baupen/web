<template>
  <button-with-modal-confirm
      :title="$t('edit_issues_button.modal_title')" color="secondary" :can-confirm="canConfirm"
      :confirm-title="storeIssuesText" :button-disabled="pendingRequestCount > 0 || issues.length === 0"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'pencil']" class="pr-1" />
      {{ $t('edit_issues_button.modal_title') }}

      <span class="btn btn-link" v-if="pendingRequestCount > 0">
        {{ pendingRequestCount }}
      </span>
    </template>

    <issues-form
        :template="template" :craftsmen="craftsmen" @update="patch = $event" />
    <image-form @update="image = $event" />

  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import IssuesForm from '../Form/IssuesForm'
import FileForm from '../Form/FileForm'
import ImageForm from '../Form/ImageForm'
import { api } from '../../services/api'
import { displaySuccess } from '../../services/notifiers'

export default {
  emits: ['save', 'save-image'],
  components: {
    ImageForm,
    FileForm,
    IssuesForm,
    ButtonWithModalConfirm,
  },
  data () {
    return {
      patch: null,
      image: null,
      prePatchedIssues: [],
      prePostedIssueImages: [],
    }
  },
  props: {
    issues: {
      type: Array,
      required: true
    },
    craftsmen: {
      type: Array,
      required: true
    }
  },
  computed: {
    pendingRequestCount: function () {
      return this.prePatchedIssues.length + this.prePostedIssueImages.length
    },
    storeIssuesText: function () {
      if (this.patch.length === 0) {
        return this.$tc('edit_issues_button.actions.save_issues', this.issues.length, { 'count': this.issues.length })
      }

      let translatedFields = []
      for (let field in this.patch) {
        if (Object.prototype.hasOwnProperty.call(this.patch, field)) {
          const translationKey = field.replace(/([A-Z])/g, '_$1')
              .toLowerCase()
          const translatedField = this.$t('issue.' + translationKey)
          translatedFields.push(translatedField)
        }
      }

      return this.$tc('edit_issues_button.actions.save_issue_fields', this.issues.length, {
        'count': this.issues.length,
        'fields': translatedFields.join(', ')
      })
    },
    template: function () {
      if (this.issues.length === 0) {
        return { }
      }

      const canary = this.issues[0];
      const sameValue = (field) => {
        let defaultValue = canary[field]
        if (!this.issues.every(i => i[field] === defaultValue)) {
          return null
        }

        return defaultValue
      }

      return {
        isMarked: sameValue('isMarked'),
        wasAddedWithClient: sameValue('wasAddedWithClient'),
        description: sameValue('description'),
        craftsman: sameValue('craftsman'),
        deadline: sameValue('deadline')
      }
    }
  },
  methods: {
    canConfirm: function () {
      return this.pendingChanges > 0
    },
    pendingChanges: function () {
      let count = this.patchPending ? 1 : 0
      count += this.file ? 1 : 0

      return count
    },
    patchPending: function () {
      return this.patch && Object.keys(this.patch).length
    },
    confirm: function () {
      if (this.patchPending) {
        this.prePatchedIssues = this.selectedIssues.map(issue => Object.assign({
          issue,
          patch: Object.assign({}, this.patch)
        }))

        this.patchIssues()
      }

      if (this.image) {
        this.prePostedIssueImages = this.selectedIssues.map(issue => Object.assign({
          issue,
          image: this.image
        }))

        this.postIssueImages()
      }
    },
    patchIssues () {
      const payload = this.prePatchedIssues[0]
      api.patch(payload.issue, payload.patch)
          .then(_ => {
                this.prePatchedIssues.shift()

                if (this.prePatchedIssues.length === 0) {
                  displaySuccess(this.$t('issue_table.messages.success.saved_issues'))
                } else {
                  this.patchIssues()
                }
              }
          )
    },
    postIssueImages () {
      const payload = this.prePostedIssueImages[0]
      api.postIssueImage(payload.issue, payload.image)
          .then(_ => {
                this.prePostedIssueImages.shift()

                if (this.prePostedIssueImages.length === 0) {
                  displaySuccess(this.$t('issue_table.messages.success.save_issue_images'))
                } else {
                  this.postIssueImages()
                }
              }
          )
    },
  },
}
</script>
