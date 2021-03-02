<template>
  <button-with-modal-confirm
      :button-disabled="disabled" :title="$t('actions.filter_issues')"
      :confirm-title="$t('actions.set_filter')"
      @confirm="confirm"
      :can-abort="customFilterActive"
      :abort-title="$t('actions.reset_filter')"
      @abort="reset">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'filter']" class="pr-1" />
      {{ $t('actions.filter') }}
    </template>

    <issue-filter-form
        :maps="maps" :craftsmen="craftsmen"
        :template="formTemplate" :configuration-template="formConfigurationTemplate"
        @update="filter = $event" @update-configuration="configuration = $event" />
  </button-with-modal-confirm>
</template>

<script>

import ButtonWithModalConfirm from '../Library/Behaviour/ButtonWithModalConfirm'
import CraftsmanForm from '../Form/CraftsmanForm'
import IssueFilterForm from '../Form/IssueFilterForm'

export default {
  emits: ['update', 'update-configuration', 'reset'],
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
    default: {
      type: Object,
      required: true
    },
    template: {
      type: Object,
      required: false
    },
    defaultConfiguration: {
      type: Object,
      required: true
    },
    configurationTemplate: {
      type: Object,
      required: false
    },
    disabled: {
      type: Boolean,
      required: true
    },
  },
  computed: {
    customFilterActive: function () {
      return !!(this.template && this.configurationTemplate)
    },
    formTemplate: function () {
      return this.template ?? this.default
    },
    formConfigurationTemplate: function () {
      return this.configurationTemplate ?? this.defaultConfiguration
    }
  },
  methods: {
    reset: function () {
      this.$emit('update', null)
      this.$emit('update-configuration', null)
    },
    confirm: function () {
      this.$emit('update', this.filter)
      this.$emit('update-configuration', this.configuration)
    }
  },
}
</script>
