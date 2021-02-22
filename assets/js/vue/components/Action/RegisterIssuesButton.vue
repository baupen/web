<template>
  <button class="btn btn-primary"
          :disabled="preRegisterIssues.length > 0 || issues.length === 0"
          @click="registerSelectedIssues">
    {{ $t('foyer.actions.register_issues') }}
    <span v-if="preRegisterIssues.length > 0">{{ preRegisterIssues.length }}</span>
  </button>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import MapForm from '../Form/MapForm'
import FileForm from '../Form/FileForm'
import { displaySuccess } from '../../services/notifiers'

export default {
  emits: ['registered'],
  components: {
    FileForm,
    MapForm,
    ButtonWithModalConfirm,
  },
  data () {
    return {
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
    }
  },
  methods: {
    registerSelectedIssues: function () {
      const nowString = (new Date()).toISOString()
      this.preRegisterIssues = this.issues.map(issue => {
        return {
          issue,
          patch: {
            registeredAt: nowString,
            registeredBy: this.constructionManagerIri
          }
        }
      })

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
                } else {
                  this.processUnregisteredIssues()
                }
              }
          )

    }
  }
}
</script>
