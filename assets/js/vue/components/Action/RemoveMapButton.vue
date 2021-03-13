<template>
  <button-with-modal-confirm
      color="danger"
      :title="$t('_action.remove_map')" :can-confirm="canConfirm"
      @shown="loadIssueSummary"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']" />
    </template>

    <p v-if="issueSummary">
      {{$t("issue._plural")}}: <issue-summary-badges :summary="issueSummary" />
    </p>

    <p class="alert alert-info">
      {{ $t('_action.remove_map_help') }}
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
      canConfirm: false
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    map: {
      type: Object,
      required: true
    }
  },
  methods: {
    confirm: function () {
      api.delete(this.map, this.$t('_action.messages.success.map_removed'))

      // reset state for next display
      this.issueSummary = null
    },
    loadIssueSummary: function () {
      api.getIssuesSummary(this.constructionSite, { map: this.map['@id'] })
          .then(issueSummary => {
            this.issueSummary = issueSummary
          })
    }
  }
}
</script>
