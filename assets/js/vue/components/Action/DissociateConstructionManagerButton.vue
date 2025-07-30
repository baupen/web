<template>
  <button-with-modal-confirm
      color="danger"
      :title="$t('_action.dissociate_construction_manager.title')"
      @confirm="confirm" :can-confirm="canConfirm" @shown="checkCanRemove">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'trash']" />
    </template>

    <loading-indicator v-if="canRemove === null" />
    <p class="alert alert-info" v-else-if="canRemove === false">
      {{ $t('_action.dissociate_construction_manager.cannot_remove_help') }}
    </p>
    <p class="alert alert-info" v-else>
      {{ $t('_action.dissociate_construction_manager.remove_help') }}
    </p>

  </button-with-modal-confirm>
</template>

<script>

import {api, iriToId} from '../../services/api'
import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import DeleteForm from '../Form/DeleteForm'
import LoadingIndicator from "../Library/View/LoadingIndicator.vue";

export default {
  emits: ['dissociated'],
  components: {
    LoadingIndicator,
    DeleteForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      canRemove: null,
      patching: false
    }
  },
  props: {
    constructionSite: {
      type: Object,
      required: true
    },
    constructionManager: {
      type: Object,
      required: true
    }
  },
  computed: {
    canConfirm: function () {
      return this.canRemove === true
    },
    query: function () {
      return {
        constructionSite: iriToId(this.constructionSite['@id']),
        createdBy: iriToId(this.constructionManager['@id']),
      }
    }
  },
  methods: {
    checkCanRemove: function () {
      api.getIssueEventsQuery(this.query).then(events => {
        this.canRemove = events.length === 0
      })
    },
    confirm: function () {
      this.patching = true
      const constructionManagers = this.constructionSite.constructionManagers.filter(cm => cm !== this.constructionManager['@id'])
      api.patch(this.constructionSite, { constructionManagers }, this.$t('_action.dissociate_construction_manager.dissociated'))
        .then(_ => {
          this.$emit('dissociated')
          this.patching = false
        })
    }
  },
}
</script>
