<template>
  <button-with-modal-confirm
      color="secondary" :title="title"
      @confirm="confirm" :can-confirm="!patching">
    <template v-slot:button-content>
      <font-awesome-icon :icon="icon"/>
    </template>

    <p class="alert alert-info whitespace-break">
      {{ help }}
    </p>

  </button-with-modal-confirm>
</template>

<script>

import {api} from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import DeleteForm from '../Form/DeleteForm'
import IssueSummaryBadges from '../View/IssueSummaryBadges'

export default {
  components: {
    ButtonWithModalConfirm
  },
  data() {
    return {
      patching: false
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
  },
  methods: {
    confirm: function () {
      this.patching = true
      let successMessage = this.constructionSite.isArchived ?
          this.$t('_action.archive_construction_site.unarchived') :
          this.$t('_action.archive_construction_site.archived')
      api.patch(this.constructionSite, {isArchived: !this.constructionSite.isArchived}, successMessage)
          .then(_ => {
            this.patching = false
          })
    },
  },
  computed: {
    title: function () {
      return this.constructionSite.isArchived ?
          this.$t('_action.archive_construction_site.title_unarchive') :
          this.$t('_action.archive_construction_site.title_archive')
    },
    help: function () {
      return this.constructionSite.isArchived ?
          this.$t('_action.archive_construction_site.help_unarchive') :
          this.$t('_action.archive_construction_site.help_archive')
    },
    icon: function () {
      return this.constructionSite.isArchived ?
          ['fal', 'box-open-full'] :
          ['fal', 'archive']
    }
  }
}
</script>
