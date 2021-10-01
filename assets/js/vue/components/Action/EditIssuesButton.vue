<template>
  <button-with-modal-confirm
      ref="modal"
      :title="$t('_action.edit_issues.title')" color="primary" :can-confirm="canConfirm"
      :confirm-title="confirmTitle" :button-disabled="pendingRequestCount > 0 || issues.length === 0"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'pencil']" class="pr-1" />
      {{ $t('_action.edit_issues.title') }}

      <span v-if="pendingRequestCount > 0">
        {{ pendingRequestCount }}
      </span>
    </template>

    <issues-form
        ref="issues-form"
        :enable-state-edit="enableStateEdit"
        :template="template" :craftsmen="craftsmen" @update="patch = $event" @confirm="$refs['modal'].confirm()" />
    <image-form @update="image = $event" />

  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import IssuesForm from '../Form/IssuesForm'
import ImageForm from '../Form/ImageForm'
import { api } from '../../services/api'
import { displaySuccess } from '../../services/notifiers'

export default {
  emits: ['save', 'save-image'],
  components: {
    ImageForm,
    IssuesForm,
    ButtonWithModalConfirm,
  },
  data () {
    return {
      patch: null,
      image: null,
      show: true,
      prePatchedIssues: [],
      prePostedIssueImages: []
    }
  },
  props: {
    issues: {
      type: Array,
      default: []
    },
    craftsmen: {
      type: Array,
      default: []
    },
    constructionManagerIri: {
      type: String,
      required: true
    },
    enableStateEdit: {
      type: Boolean,
      default: false
    }
  },
  computed: {
    canConfirm: function () {
      return this.pendingChanges > 0
    },
    confirmTitle: function () {
      if (!this.patchPending) {
        return this.$tc('_action.edit_issues.save_issues', this.issues.length, { 'count': this.issues.length })
      }

      let translatedFields = []
      for (let field in this.patch) {
        if (Object.prototype.hasOwnProperty.call(this.patch, field)) {
          if (field === 'isResolved' || field === 'isClosed') {
            const translatedField = this.$t('issue.state.' + field.substr(2).toLowerCase())
            translatedFields.push(translatedField)
          } else {
            const translationKey = field.replace(/([A-Z])/g, '_$1')
                .toLowerCase()
            const translatedField = this.$t('issue.' + translationKey)
            translatedFields.push(translatedField)
          }
        }
      }

      return this.$tc('_action.edit_issues.save_issue_fields', this.issues.length, {
        'count': this.issues.length,
        'fields': translatedFields.join(', ')
      })
    },
    pendingChanges: function () {
      let count = this.patchPending ? 1 : 0
      count += this.image ? 1 : 0

      return count
    },
    patchPending: function () {
      return this.patch && Object.keys(this.patch).length
    },
    pendingRequestCount: function () {
      return this.prePatchedIssues.length + this.prePostedIssueImages.length
    },
    template: function () {
      if (this.issues.length === 0) {
        return {}
      }

      const canary = this.issues[0]
      const sameValue = (field) => {
        let defaultValue = canary[field]
        if (!this.issues.every(i => i[field] === defaultValue)) {
          return null
        }

        return defaultValue
      }

      const sameState = (field) => {
        const fieldName = field + 'At'
        let defaultValue = !!canary[fieldName]
        if (!this.issues.every(i => !!i[fieldName] === defaultValue)) {
          return null
        }

        return defaultValue
      }

      return {
        isMarked: sameValue('isMarked'),
        wasAddedWithClient: sameValue('wasAddedWithClient'),
        description: sameValue('description'),
        craftsman: sameValue('craftsman'),
        deadline: sameValue('deadline'),
        isResolved: sameState('resolved'),
        isClosed: sameState('closed')
      }
    }
  },
  methods: {
    selectDescription: function () {
      // called from parent
      this.$nextTick(() => {
        this.$refs['issues-form'].selectDescription()
      })
    },
    transformStatePatch: function (patch, patchPropertyName, stateName, owner, issue) {
      if (Object.prototype.hasOwnProperty.call(patch, patchPropertyName)) {
        const dateTimeStateName = stateName + 'At'
        const dateTimeTargetValue = patch[patchPropertyName] ? (new Date()).toISOString() : null

        // only patch if state different
        if (!!issue[dateTimeStateName] !== !!dateTimeTargetValue) {
          patch[stateName + 'At'] = dateTimeTargetValue
          patch[stateName + 'By'] = patch[patchPropertyName] ? owner : null
        }

        delete patch[patchPropertyName]
      }
    },
    confirm: function () {
      if (this.patchPending) {
        this.prePatchedIssues = this.issues.map(issue => {
          let patch = Object.assign({}, this.patch)

          this.transformStatePatch(patch, 'isResolved', 'resolved', issue.craftsman, issue)
          this.transformStatePatch(patch, 'isClosed', 'closed', this.constructionManagerIri, issue)

          if (patch.length === 0) {
            return null
          }

          return {
            issue,
            patch
          }
        }).filter(p => p)

        this.patchIssues()
      }

      if (this.image) {
        this.prePostedIssueImages = this.issues.map(issue => Object.assign({
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
                  displaySuccess(this.$t('_action.edit_issues.saved'))
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
                  displaySuccess(this.$t('_action.edit_issues.replaced_issue_images'))
                } else {
                  this.postIssueImages()
                }
              }
          )
    },
  },
}
</script>
