<template>
  <button-with-modal-confirm
      :title="$t('_action.filter_issues.title')"
      :confirm-title="$t('_action.filter_issues.confirm')"
      @confirm="confirm"
      :can-abort="customFilterActive"
      :abort-title="$t('_action.filter_issues.reset')"
      :active="customFilterActive"
      @abort="reset">
    <template v-slot:button-content>
      <font-awesome-icon :icon="['fal', 'filter']" class="pe-1" />
      {{ $t('_action.filter_issues.title') }}
    </template>

    <issue-filter-form
        :maps="maps" :craftsmen="craftsmen" :construction-managers="constructionManagers"
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
    constructionManagers: {
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
    },
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
