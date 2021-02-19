<template>
  <button-with-modal-confirm
      :button-disabled="disabled" :title="$t('actions.filter_issues')"
      :confirm-title="$t('actions.set_filter')"
      @confirm="confirm">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'filter']" class="pr-1" />
      {{ $t('actions.filter') }}
    </template>

    <issue-filter-form
        :maps="maps" :craftsmen="craftsmen"
        :template="template" :configuration-template="configurationTemplate"
        @update="filter = $event" @update-configuration="configuration = $event" />
  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import CraftsmanForm from '../Form/CraftsmanForm'
import IssueFilterForm from '../Form/IssueFilterForm'

export default {
  emits: ['update', 'update-configuration'],
  components: {
    IssueFilterForm,
    CraftsmanForm,
    ButtonWithModalConfirm
  },
  data () {
    return {
      filter: null,
      configuration: null,
    }
  },
  props: {
    maps: {
      type: Array,
      default: []
    },
    craftsmen: {
      type: Array,
      default: []
    },
    template: {
      type: Object,
      required: true
    },
    configurationTemplate: {
      type: Object,
      required: true
    },
    disabled: {
      type: Boolean,
      required: true
    },
  },
  methods: {
    confirm: function () {
      this.$emit('update', this.filter)
      this.$emit('update-configuration', this.configuration)
    }
  },
}
</script>
