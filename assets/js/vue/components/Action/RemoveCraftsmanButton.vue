<template>
  <button-with-modal-confirm
      color="danger"
      :title="$t('actions.remove_craftsman')" :can-confirm="canConfirm"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']" />
    </template>

    <p class="alert alert-info">
      {{ $t('actions.remove_craftsman_help') }}
    </p>

    <delete-form @update="canConfirm = $event" />

  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Behaviour/ButtonWithModalConfirm'
import CraftsmanForm from '../Edit/CraftsmanForm'
import { api } from '../../services/api'
import DeleteForm from '../Edit/DeleteForm'

export default {
  components: {
    DeleteForm,
    CraftsmanForm,
    ButtonWithModalConfirm
  },
  emits: ['removed'],
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
      api.delete(this.craftsman, this.$t('actions.messages.success.craftsman_removed'))
          .then(_ => {
            this.$emit('removed', this.craftsman)
          })

      // reset state for next display
      this.issueSummary = null
    }
  },
  mounted () {
    api.getIssuesSummary(this.constructionSite, { craftsman: this.craftsman['@id'] })
        .then(issueSummary => {
          this.issueSummary = issueSummary
        })
  }
}
</script>
