<template>
  <button-with-modal-confirm
      color="danger"
      :title="$t('_action.remove_craftsman.title')" :can-confirm="canConfirm"
      @shown="loadIssueSummary"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']" />
    </template>

    <p v-if="issueSummary">
      {{ $t('issue._plural') }}:
      <issue-summary-badges :summary="issueSummary" />
    </p>

    <p class="alert alert-info">
      {{ $t('_action.remove_craftsman.help') }}
    </p>

    <delete-form @update="canConfirm = $event" />

  </button-with-modal-confirm>
</template>

<script>

import { api } from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import DeleteForm from '../Form/DeleteForm'
import IssueSummaryBadges from '../View/IssueSummaryBadges'

export default {
  components: {
    IssueSummaryBadges,
    DeleteForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      issueSummary: null,
      craftsmanPatch: null,
      canConfirm: false
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    craftsman: {
      type: Object,
      required: true
    }
  },
  methods: {
    confirm: function () {
      api.delete(this.craftsman, this.$t('_action.remove_craftsman.removed'))

      // reset state for next display
      this.issueSummary = null
    },
    loadIssueSummary: function () {
      api.getIssuesSummary(this.constructionSite, { craftsman: this.craftsman['@id'] })
          .then(issueSummary => {
            this.issueSummary = issueSummary
          })
    }
  },
}
</script>
