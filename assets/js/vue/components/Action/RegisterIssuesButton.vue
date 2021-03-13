<template>
  <button class="btn btn-primary"
          :disabled="preRegisterIssues.length > 0 || issues.length === 0"
          @click="registerSelectedIssues">
    {{ $tc('foyer.action.register_issues', this.issues.length) }}
  </button>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import MapForm from '../Form/MapForm'
import FileForm from '../Form/FileForm'
import { displaySuccess, displayWarning } from '../../services/notifiers'

export default {
  emits: ['registered'],
  components: {
    FileForm,
    MapForm,
    ButtonWithModalConfirm,
  },
  data () {
    return {
      totalPreRegisterIssues: 0,
      preRegisterIssues: [],
    }
  },
  props: {
    constructionManagerIri: {
      type: String,
      required: true
    },
    issues: {
      type: Array,
      required: true
    },
  },
  computed: {
    canConfirm: function () {
      return !!this.post
    },
  },
  methods: {
    registerSelectedIssues: function () {
      const nowString = (new Date()).toISOString()

      let filteredIssues = this.issues.filter(i => i.craftsman)
      let invalidIssueCount = this.issues.length - filteredIssues.length
      if (invalidIssueCount > 0) {
        displayWarning(this.$tc('_action.messages.registration_skipped_no_craftsman', invalidIssueCount))
      }

      if (filteredIssues.length === 0) {
        return;
      }

      this.preRegisterIssues = filteredIssues.map(issue => {
        return {
          issue,
          patch: {
            registeredAt: nowString,
            registeredBy: this.constructionManagerIri
          }
        }
      })
      this.totalPreRegisterIssues = this.preRegisterIssues.length

      this.processUnregisteredIssues()
    },
    processUnregisteredIssues () {
      const payload = this.preRegisterIssues[0]
      api.patch(payload.issue, payload.patch)
          .then(_ => {
                this.$emit('registered', payload.issue)
                this.preRegisterIssues.shift()

                if (this.preRegisterIssues.length === 0) {
                  displaySuccess(this.$t('foyer.messages.success.registered_issues'))
                  this.totalPreRegisterIssues = 0
                } else {
                  this.processUnregisteredIssues()
                }
              }
          )

    }
  }
}
</script>
