<template>
  <button-with-modal-confirm
      color="danger"
      :title="$t('_action.remove_issues')" :can-confirm="canConfirm"
      :button-disabled="issues.length === 0"
      :confirm-title="confirmTitle"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']" />
      <span class="btn btn-link" v-if="preDeletedIssues > 0">
        {{ preDeletedIssues }}
      </span>
    </template>

    <p class="alert alert-info">
      {{ $t('_action.remove_issues_help') }}
    </p>

    <delete-form @update="canConfirm = $event" />

  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import DeleteForm from '../Form/DeleteForm'
import { displaySuccess } from '../../services/notifiers'

export default {
  emits: ['removed'],
  components: {
    DeleteForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      canConfirm: false,
      preDeletedIssues: []
    }
  },
  props: {
    issues: {
      type: Array,
      default: []
    }
  },
  computed: {
    confirmTitle: function () {
      return this.$tc('_action.remove_issues', this.issues.length, {'count': this.issues.length})
    },
  },
  methods: {
    confirm: function () {
      this.preDeletedIssues = [...this.issues]

      this.deleteIssues()
    },
    deleteIssues () {
      const issue = this.preDeletedIssues[0]
      api.delete(issue)
          .then(_ => {
                this.preDeletedIssues.shift()
                this.$emit('removed', issue)

                if (this.preDeletedIssues.length === 0) {
                  displaySuccess(this.$t('_action.messages.success.issues_removed'))
                } else {
                  this.deleteIssues()
                }
              }
          )
    },
  }
}
</script>
